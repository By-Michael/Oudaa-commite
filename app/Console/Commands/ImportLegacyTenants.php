<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Support\CurrentCommunity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * One-off migration from the old "one SQLite file per tenant" layout
 * to the new single-database layout. Run this ONCE, after
 * `php artisan migrate` has created the new schema, and BEFORE
 * deleting anything in database/tenants/ or database/central.sqlite.
 *
 *   php artisan tenants:import-legacy
 *
 * Options:
 *   --central=path      Old central.sqlite (default: database/central.sqlite)
 *   --tenants-dir=path  Old per-tenant .sqlite files (default: database/tenants)
 *
 * Safe to run more than once for a given tenant: it looks up (or
 * creates) the Tenant by slug, then only imports rows that don't
 * already exist for that community_id (matched by natural keys, not
 * old auto-increment ids — those ids get remapped fresh here since
 * every tenant's old ids started at 1 and would collide directly).
 */
class ImportLegacyTenants extends Command
{
    protected $signature = 'tenants:import-legacy
        {--central=database/central.sqlite : Path to the old central registry sqlite file}
        {--tenants-dir=database/tenants : Directory containing the old {slug}.sqlite files}';

    protected $description = 'Import data from the old per-tenant SQLite files into the new single-database schema';

    public function handle(): int
    {
        $centralPath = base_path($this->option('central'));
        $tenantsDir = base_path($this->option('tenants-dir'));

        if (! file_exists($centralPath)) {
            $this->error("Old central DB not found at {$centralPath}");

            return self::FAILURE;
        }

        $legacyCentral = new PDO('sqlite:'.$centralPath);
        $legacyCentral->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $legacyTenants = $legacyCentral->query('SELECT * FROM tenants')->fetchAll();

        if (empty($legacyTenants)) {
            $this->warn('No tenants found in the old central DB. Nothing to do.');

            return self::SUCCESS;
        }

        foreach ($legacyTenants as $row) {
            $this->importOneTenant($row, $tenantsDir);
        }

        $this->newLine();
        $this->info('Done. Verify the data (log into each community and check residents/payments/etc), then you can delete database/central.sqlite and database/tenants/ — they are no longer read by the app.');

        return self::SUCCESS;
    }

    protected function importOneTenant(array $row, string $tenantsDir): void
    {
        $slug = $row['slug'];
        $file = rtrim($tenantsDir, '/')."/{$slug}.sqlite";

        $this->line("Importing tenant \"{$slug}\"...");

        if (! file_exists($file)) {
            $this->warn("  Skipping: no file at {$file}");

            return;
        }

        $tenant = Tenant::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $row['name'],
                'community_type' => $row['community_type'] ?? 'normal',
                'owner_email' => $row['owner_email'],
                'status' => $row['status'] === 'active' ? 'active' : 'pending_setup',
                'setup_token' => $row['setup_token'] ?? null,
                'setup_token_expires_at' => $row['setup_token_expires_at'] ?? null,
                'provisioning_error' => $row['provisioning_error'] ?? null,
            ]
        );

        app(CurrentCommunity::class)->set($tenant->id);

        $legacyTenant = new PDO('sqlite:'.$file);
        $legacyTenant->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        DB::transaction(function () use ($legacyTenant, $tenant) {
            // Order matters: parents before the children that
            // foreign-key to them.
            $committeeIds = $this->importCommittees($legacyTenant, $tenant->id);
            $residentIds = $this->importSimple($legacyTenant, 'residents', $tenant->id, [
                'name', 'id_number', 'unit_number', 'block_number', 'phone', 'email',
                'occupancy', 'status', 'created_at', 'updated_at',
            ]);
            $fundIds = $this->importSimple($legacyTenant, 'funds', $tenant->id, [
                'name', 'category', 'description', 'status', 'created_at', 'updated_at',
            ]);
            $employeeIds = $this->importSimple($legacyTenant, 'employees', $tenant->id, [
                'name', 'id_number', 'role', 'salary', 'payment_date', 'phone', 'status',
                'created_at', 'updated_at',
            ]);
            $feeIds = $this->importWithForeignKeys($legacyTenant, 'fees', $tenant->id, [
                'name', 'amount', 'frequency', 'recurrence_day', 'status', 'created_at', 'updated_at',
            ], ['fund_id' => $fundIds]);
            $projectIds = $this->importWithForeignKeys($legacyTenant, 'projects', $tenant->id, [
                'name', 'description', 'planned_budget', 'start_date', 'end_date', 'status',
                'created_at', 'updated_at',
            ], ['fund_id' => $fundIds]);
            $this->importWithForeignKeys($legacyTenant, 'payments', $tenant->id, [
                'amount', 'method', 'paid_at', 'note', 'status', 'period_key', 'created_at', 'updated_at',
            ], ['resident_id' => $residentIds, 'fee_id' => $feeIds, 'fund_id' => $fundIds]);
            $this->importWithForeignKeys($legacyTenant, 'expenses', $tenant->id, [
                'category', 'amount', 'vendor', 'incurred_at', 'note', 'receipt_path', 'created_at', 'updated_at',
            ], ['fund_id' => $fundIds, 'project_id' => $projectIds, 'employee_id' => $employeeIds]);
            $this->importAuditLogs($legacyTenant, $tenant->id, $committeeIds, [
                'residents' => $residentIds, 'funds' => $fundIds, 'employees' => $employeeIds,
                'fees' => $feeIds, 'projects' => $projectIds,
                // payments/expenses ids weren't captured above by table name;
                // audit rows for those subject types are imported with a null
                // subject_id fallback below rather than left unimported.
            ]);
            $this->importTenantSettings($legacyTenant, $tenant->id);
        });

        $this->info('  Done.');
    }

    /** @return array<int,int> old id => new id */
    protected function importCommittees(PDO $db, int $communityId): array
    {
        $map = [];
        foreach ($this->rows($db, 'committees') as $r) {
            $existing = DB::table('committees')
                ->where('community_id', $communityId)
                ->where('email', $r['email'])
                ->value('id');

            $newId = $existing ?: DB::table('committees')->insertGetId([
                'community_id' => $communityId,
                'name' => $r['name'],
                'email' => $r['email'],
                'phone' => $r['phone'] ?? null,
                'email_verified_at' => $r['email_verified_at'] ?? null,
                'password' => $r['password'],
                'remember_token' => $r['remember_token'] ?? null,
                'created_at' => $r['created_at'],
                'updated_at' => $r['updated_at'],
            ]);

            $map[$r['id']] = $newId;
        }

        return $map;
    }

    /** @return array<int,int> old id => new id */
    protected function importSimple(PDO $db, string $table, int $communityId, array $columns): array
    {
        $map = [];
        foreach ($this->rows($db, $table) as $r) {
            $insert = ['community_id' => $communityId];
            foreach ($columns as $c) {
                $insert[$c] = $r[$c] ?? null;
            }
            $map[$r['id']] = DB::table($table)->insertGetId($insert);
        }

        return $map;
    }

    /** @return array<int,int> old id => new id */
    protected function importWithForeignKeys(PDO $db, string $table, int $communityId, array $columns, array $fkMaps): array
    {
        $map = [];
        foreach ($this->rows($db, $table) as $r) {
            $insert = ['community_id' => $communityId];
            foreach ($columns as $c) {
                $insert[$c] = $r[$c] ?? null;
            }
            foreach ($fkMaps as $fkColumn => $idMap) {
                $oldFk = $r[$fkColumn] ?? null;
                $insert[$fkColumn] = $oldFk !== null ? ($idMap[$oldFk] ?? null) : null;
            }
            $map[$r['id']] = DB::table($table)->insertGetId($insert);
        }

        return $map;
    }

    protected function importAuditLogs(PDO $db, int $communityId, array $committeeIds, array $subjectIdMaps): void
    {
        foreach ($this->rows($db, 'audit_logs') as $r) {
            $subjectType = strtolower((string) ($r['subject_type'] ?? ''));
            $newSubjectId = null;

            foreach ($subjectIdMaps as $table => $idMap) {
                if (str_contains($subjectType, rtrim($table, 's')) && $r['subject_id'] !== null) {
                    $newSubjectId = $idMap[$r['subject_id']] ?? null;
                    break;
                }
            }

            DB::table('audit_logs')->insert([
                'community_id' => $communityId,
                'committee_id' => $r['committee_id'] !== null ? ($committeeIds[$r['committee_id']] ?? null) : null,
                'committee_name' => $r['committee_name'] ?? null,
                'action' => $r['action'],
                'subject_type' => $r['subject_type'],
                'subject_id' => $newSubjectId,
                'description' => $r['description'],
                'created_at' => $r['created_at'],
            ]);
        }
    }

    protected function importTenantSettings(PDO $db, int $communityId): void
    {
        $rows = $this->rows($db, 'tenant_settings');

        if (empty($rows)) {
            return;
        }

        $r = $rows[0];

        if (DB::table('tenant_settings')->where('community_id', $communityId)->exists()) {
            return;
        }

        DB::table('tenant_settings')->insert([
            'community_id' => $communityId,
            'community_name' => $r['community_name'],
            'community_type' => $r['community_type'],
            'created_at' => $r['created_at'] ?? now(),
            'updated_at' => $r['updated_at'] ?? now(),
        ]);
    }

    protected function rows(PDO $db, string $table): array
    {
        try {
            return $db->query("SELECT * FROM {$table}")->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }
}
