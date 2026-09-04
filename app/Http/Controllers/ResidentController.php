<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\TenantSetting;
use App\Support\CurrentCommunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Support\Export\Exportable;

class ResidentController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        $residents = $this->filtered($request)
            ->orderBy('name')
            ->orderBy('unit_number')
            ->orderBy('block_number')
            ->orderBy('id_number')
            ->orderBy('phone')
            ->paginate(15)
            ->withQueryString();

        return view('residents.index', compact('residents'));
    }

    /**
     * Filters shared by the index page and both export endpoints, so a
     * "download what I'm looking at" export always matches the screen.
     */
    private function filtered(Request $request)
    {
        return Resident::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('unit_number', 'like', "%{$request->search}%")
                ->orWhere('block_number', 'like', "%{$request->search}%")
                ->orWhere('id_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->occupancy, fn ($q) => $q->where('occupancy', $request->occupancy))
            ->when($request->resident_id, fn ($q) => $q->where('id', $request->resident_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));
    }

    private function filterSummary(Request $request): array
    {
        $lines = [];
        if ($request->search) $lines[] = "Search: {$request->search}";
        if ($request->status) $lines[] = "Status: {$request->status}";
        if ($request->occupancy) $lines[] = "Occupancy: {$request->occupancy}";
        if ($request->resident_id) $lines[] = 'Resident #'.$request->resident_id;
        if ($request->date_from) $lines[] = "From: {$request->date_from}";
        if ($request->date_to) $lines[] = "To: {$request->date_to}";

        return $lines;
    }

    private function exportRows(Request $request): array
    {
        $headers = ['Name', 'Unit', 'Block', 'ID Number', 'Phone', 'Email', 'Occupancy', 'Status', 'Added'];

        $rows = $this->filtered($request)->orderBy('name')->get()->map(fn (Resident $r) => [
            $r->name, $r->unit_number, $r->block_number ?: '—', $r->id_number,
            $r->phone ?: '—', $r->email ?: '—', ucfirst($r->occupancy), ucfirst($r->status),
            $r->created_at?->format('Y-m-d'),
        ])->all();

        return [$headers, $rows];
    }

    public function exportExcelIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportExcel('Residents', $headers, $rows, 'residents', $this->filterSummary($request));
    }

    public function exportPdfIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportPdf('Residents', $headers, $rows, 'residents', $this->filterSummary($request));
    }

    public function create()
    {
        return view('residents.form', [
            'resident' => new Resident(),
            'isCondo' => $this->isCondo(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = 'active'; // new residents always start active — status is only ever changed later, via edit.
        Resident::create($data);

        return redirect()->route('residents.index')->with('status', __('Resident added.'));
    }

    public function bulkImportForm()
    {
        return view('residents.bulk-import', [
            'isCondo' => $this->isCondo(),
        ]);
    }

    /**
     * Downloadable .xlsx starter template. Columns match validated()
     * below exactly (minus block_number when the community isn't a
     * condo), so anything filled in correctly here imports cleanly.
     */
    public function bulkImportTemplate()
    {
        $isCondo = $this->isCondo();

        $headers = ['Name', 'Unit Number'];
        if ($isCondo) {
            $headers[] = 'Block Number';
        }
        $headers = array_merge($headers, ['ID Number', 'Phone', 'Email', 'Occupancy']);

        $example = ['Abebe Kebede', '12'];
        if ($isCondo) {
            $example[] = 'A';
        }
        $example = array_merge($example, ['ETH-0192837', '0911223344', 'abebe@example.com', 'owner']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Residents');

        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($example, null, 'A2');

        $lastCol = chr(ord('A') + count($headers) - 1);

        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A1:{$lastCol}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('14919B');
        $sheet->getStyle("A2:{$lastCol}2")->getFont()->setItalic(true)->getColor()->setRGB('667085');

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }

        // Dropdown restricting the Occupancy column to valid values.
        $occupancyCol = chr(ord('A') + count($headers) - 1);
        for ($row = 2; $row <= 200; $row++) {
            $validation = $sheet->getCell("{$occupancyCol}{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Invalid occupancy');
            $validation->setError('Please choose "owner" or "renter" from the list.');
            $validation->setFormula1('"owner,renter"');
        }

        $sheet->getStyle("A1:{$lastCol}200")->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'resident-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * All-or-nothing: every row in the file is validated first (including
     * duplicate ID numbers within the file itself, not just against the
     * database) before anything is written, so a bad row can't leave a
     * partial import behind. New residents from a bulk import always
     * start active, same as one added through the regular form.
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $isCondo = $this->isCondo();

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => __('Could not read that file. Please use the downloaded template and try again.')]);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows); // header row

        $seenIdNumbers = [];
        $rowErrors = [];
        $toInsert = [];
        $rowNumber = 1; // header was row 1

        foreach ($rows as $row) {
            $rowNumber++;

            $col = 0;
            $name = trim((string) ($row[$col++] ?? ''));
            $unitNumber = trim((string) ($row[$col++] ?? ''));
            $blockNumber = $isCondo ? trim((string) ($row[$col++] ?? '')) : null;
            $idNumber = trim((string) ($row[$col++] ?? ''));
            $phone = trim((string) ($row[$col++] ?? ''));
            $email = trim((string) ($row[$col++] ?? ''));
            $occupancy = strtolower(trim((string) ($row[$col++] ?? '')));

            // Skip fully blank rows (e.g. trailing empty rows in the sheet).
            if ($name === '' && $unitNumber === '' && $idNumber === '') {
                continue;
            }

            $candidate = [
                'name' => $name,
                'unit_number' => $unitNumber,
                'block_number' => $blockNumber !== '' ? $blockNumber : null,
                'id_number' => $idNumber,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'occupancy' => $occupancy,
            ];

            $validator = Validator::make($candidate, [
                'name' => ['required', 'string', 'max:255'],
                'unit_number' => ['required', 'string', 'max:50'],
                'block_number' => ['nullable', 'string', 'max:50'],
                'id_number' => [
                    'required', 'string', 'max:100',
                    Rule::unique('residents', 'id_number')
                        ->where(fn ($q) => $q->where('community_id', app(CurrentCommunity::class)->id())),
                ],
                'phone' => ['nullable', 'string', 'max:50'],
                'email' => ['nullable', 'email', 'max:255'],
                'occupancy' => ['required', 'in:owner,renter'],
            ]);

            if ($validator->fails()) {
                $rowErrors[] = "Row {$rowNumber}: ".implode(' ', $validator->errors()->all());

                continue;
            }

            if ($idNumber !== '' && isset($seenIdNumbers[$idNumber])) {
                $rowErrors[] = "Row {$rowNumber}: ID Number \"{$idNumber}\" is duplicated in this file (first seen on row {$seenIdNumbers[$idNumber]}).";

                continue;
            }
            $seenIdNumbers[$idNumber] = $rowNumber;

            $candidate['status'] = 'active';
            $toInsert[] = $candidate;
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => __('That file has no resident rows below the header. Nothing was imported.')]);
        }

        if (! empty($rowErrors)) {
            return back()->withErrors(['bulk_import' => $rowErrors])->withInput();
        }

        DB::transaction(function () use ($toInsert) {
            foreach ($toInsert as $data) {
                Resident::create($data);
            }
        });

        return redirect()->route('residents.index')
            ->with('status', count($toInsert).' resident'.(count($toInsert) === 1 ? '' : 's').' imported.');
    }

    public function edit(Request $request)
    {
        $resident = Resident::findOrFail($request->route('resident'));

        return view('residents.form', [
            'resident' => $resident,
            'isCondo' => $this->isCondo(),
        ]);
    }

    public function update(Request $request)
    {
        $resident = Resident::findOrFail($request->route('resident'));
        $data = $this->validated($request, $resident);
        $data['status'] = $request->input('status', $resident->status); // status only settable here, on edit
        $resident->update($data);

        return redirect()->route('residents.index')->with('status', __('Resident updated.'));
    }

    /**
     * Only condo/apartment communities organize residents into blocks —
     * for a "normal" community the field is irrelevant, so we hide it
     * in the form and strip it out on save rather than just hiding it
     * cosmetically (see validated()).
     */
    private function isCondo(): bool
    {
        return TenantSetting::current()?->isCondo() ?? false;
    }

    /**
     * Deactivate is the usual way to remove a resident from active use,
     * since it keeps payment history intact. Hard delete (below) is only
     * offered for residents with no payment history at all.
     */
    public function deactivate(Request $request)
    {
        $resident = Resident::findOrFail($request->route('resident'));
        $resident->update(['status' => $resident->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', __('Resident status updated.'));
    }

    /**
     * Hard delete. Blocked when the resident has any payment history,
     * since deleting would cascade-delete those payment records —
     * such residents should be deactivated instead.
     */
    public function destroy(Request $request)
    {
        $resident = Resident::findOrFail($request->route('resident'));

        if ($resident->payments()->exists()) {
            return back()->withErrors([
                'delete' => __('This resident has payment history and cannot be deleted. Deactivate them instead.'),
            ]);
        }

        $resident->delete();

        return redirect()->route('residents.index')->with('status', __('Resident deleted.'));
    }

    private function validated(Request $request, ?Resident $resident = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'id_number' => [
                'required', 'string', 'max:100',
                Rule::unique('residents', 'id_number')
                    ->where(fn ($q) => $q->where('community_id', app(CurrentCommunity::class)->id()))
                    ->ignore($resident?->id),
            ],
            'unit_number' => ['required', 'string', 'max:50'],
            'block_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'occupancy' => ['required', 'in:owner,renter'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        if (! $this->isCondo()) {
            $data['block_number'] = null;
        }

        return $data;
    }
}
