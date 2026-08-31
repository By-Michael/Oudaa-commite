# Committee Site

A committee-only management panel (Laravel 10). There is **no resident login** —
residents, fees, funds, etc. are records the committee manages from a single
internal panel. UI is plain server-rendered Blade + hand-written CSS (no
Bootstrap/Tailwind), kept sharp and functional.

## Features (v1)

1. **Auth** — committee login/logout (session-based).
2. **Residents** — add / edit / list (name, unit, phone, owner/renter, status). No delete — deactivate instead.
3. **Fees** — define fees (name, amount, frequency: monthly/quarterly/yearly/one-time).
4. **Payments** — log a payment against a resident, optionally linked to a fee and a fund (method, date, note, status).
5. **Unpaid summary** — per fee, see which active residents have no PAID payment for the current period.
6. **Funds** — create funds (name, category, description). No delete — archive instead.
7. **Expenses** — record an expense (category, amount, vendor, date, linked fund, linked project).
8. **Dashboard** — total funds balance, total collected, total spent, recent payments & expenses.
9. **Projects** — create projects (name, description, budget, status), optionally tie each to a fund. Every expense can optionally link to a project, so a project's detail page shows budget vs. spent vs. remaining and the full expense trail against it, alongside its linked fund's balance — this is the "inter-project" view tying spend back to funds.
10. **Multi-user committee accounts** — every committee member has their own login (name, email, phone, password). Any signed-in member can add another from **Committee Members** in the sidebar. There's no separate admin role in v1 — all committee accounts are trusted equally, but every action is attributed to the person who performed it.
11. **Append-only audit log** — every create, update, deactivate/reactivate, and archive/restore across Residents, Fees, Payments, Funds, Projects, Expenses, and Committee accounts is automatically recorded with who did it and when. There are no edit/delete routes for this table anywhere in the app — it's writable only via a single `AuditLog::record()` call triggered by Eloquent model events. View it under **Audit Log** in the sidebar.

## Requirements

- PHP 8.1+
- Composer
- SQLite extension (default) — or MySQL if you prefer

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate

# SQLite is the default DB (already configured in .env.example).
touch database/database.sqlite

php artisan migrate --seed
php artisan serve
```

Visit `http://localhost:8000`.

**Default committee login (created by the seeder):**

- Email: `admin@committee.local`
- Password: `password`

There **is** an in-app profile/password screen — go to Settings from the
sidebar after logging in to change name, email, phone, or password.

## Using MySQL instead of SQLite

In `.env`, comment out `DB_CONNECTION=sqlite` and uncomment the MySQL block,
filling in your credentials, then run `php artisan migrate --seed` again.

## Notes on the data model

- Residents and Funds are **never deleted** — only deactivated/archived — so
  historical payments and expenses always keep a valid reference.
- A payment's "period" (`period_key`) is computed from the linked fee's
  frequency at the moment it's saved (e.g. `2026-08` for monthly,
  `2026-Q3` for quarterly, `2026` for yearly, `once` for one-time). The
  unpaid summary compares against the fee's *current* period.
- Dashboard's "Total Funds Balance" is `sum(PAID payments linked to a fund) - sum(expenses linked to that fund)`, summed across all active funds.

## Data integrity rules (v1.1)

A few real-world policies are enforced server-side, not just suggested in the UI:

- **A fund is required on every expense**, and on any payment marked **PAID**. This keeps "Total Collected"/"Total Spent" on the dashboard always equal to the sum of what actually moved through the funds — no orphaned money.
- **An expense linked to a project is force-locked to that project's fund** (if the project has one), even if the request tries to send a different fund. This stops a project's "spent" total from ever disagreeing with the fund it's supposed to draw from.
- **A payment's status can be corrected after the fact** (e.g. a cheque logged PENDING clears a few days later) via "Edit Status" on the Payments list. This intentionally only touches status, fund, and note — the resident, fee, and amount are locked once created. To fix those, void the entry and record a new one, so the audit trail stays honest.
- **Inactive residents remain selectable when recording a payment.** Deactivating a resident (e.g. they moved out) only hides them from new fee/unpaid-summary flows going forward — it never blocks logging a payment they still owed from before they left.
- Dates on payments and expenses can't be set in the future.

## Structure

Standard Laravel layout: `app/Models`, `app/Http/Controllers`,
`routes/web.php`, `database/migrations`, `resources/views`. No JS framework,
no build step required — `public/css/app.css` is plain CSS.

## Deploying to Render (free tier)

This repo ships a `Dockerfile` (nginx + PHP-FPM) and a `render.yaml`
Blueprint, both already tuned for Render's free tier quirks (ephemeral
disk, injected `$PORT`, secret files mounted read-only). Database is
**Aiven's free MySQL** — Render's own free Postgres expires after 30
days, Aiven's free MySQL doesn't.

1. **Create the database.** Sign up at [aiven.io](https://aiven.io),
   create a free MySQL service, and from its Overview tab grab the
   Host, Port, User (`avnadmin`), Password, and the **CA certificate**
   (download as `ca.pem`).
2. **Push this repo to GitHub** (a fresh repo — the old `defaultdb`
   SQLite file has been removed and gitignored; don't re-add it, it
   had real local data in it).
3. **In Render:** New → Blueprint → point at your GitHub repo. Render
   reads `render.yaml` and creates the web service automatically.
4. **Fill in the `sync: false` env vars** Render will prompt for:
   `APP_URL` (your Render URL, e.g. `https://oudaa-commite.onrender.com`),
   `DB_HOST`, `DB_PORT`, `DB_PASSWORD` from Aiven, and your SMTP
   credentials (`MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`,
   `MAIL_FROM_ADDRESS`, `MAIL_SUPPORT_ADDRESS`).
5. **Upload the Aiven CA cert as a Render Secret File**: in the
   service's Environment tab → Secret Files → filename
   `aiven-ca.pem`, path `/etc/secrets/aiven-ca.pem`, paste the
   contents of the `ca.pem` you downloaded. `docker/start.sh` copies
   it into `storage/certs/` with the right permissions at boot.
6. **Deploy.** The container runs migrations automatically on every
   boot (`php artisan migrate --force` in `docker/start.sh`), so the
   schema is created on first deploy — no manual seeding step needed.
   Create your first committee login by visiting the app's onboarding
   flow, or run `php artisan tinker` from the Render shell if you'd
   rather seed one directly.

Notes on the free tier: the service spins down after ~15 minutes idle
and takes 30–60s to wake on the next request; sessions use the
database driver specifically so a spin-down never logs anyone out.
Aiven's free MySQL plan is time-limited on some regions/promos, so
check current terms on aiven.io before relying on it long-term.
