## Plan: IDMS Card Data Sync Pipeline

Build a two-stage IDMS synchronization flow modeled on the existing UCR card-data pipeline: one command/job pair fetches the full upstream snapshot into `idms_card_data_staging`, and a second command/job pair merges staging into `idms_card_data` using deterministic composite-key matching plus selective field updates. The recommended approach treats the API `AllIdmsCardData` feed as the source-of-truth snapshot for staging, keeps scheduling configurable via env-backed config, and reuses existing Laravel command/job/scheduler conventions already present in the repo.

**Steps**
1. Phase 1: Establish the IDMS persistence surface.
2. Add Eloquent models for `idms_card_data_staging` and `idms_card_data` with date casts matching the new migrations and with unguarded/fillable behavior consistent with `UcrCardDataStaging`. This blocks all later steps because jobs and commands need the models.
3. Review and, if necessary, extend the new migrations to support sync safety and performance: add indexes covering the merge identity and lookup combinations (`net_id`, `ucr_id`, `student_id`) plus the fallback pairs (`net_id + ucr_id`, `net_id + student_id`, `ucr_id + student_id`), and index `library_card_number` only if later needed for diagnostics. If migrations have already been run in shared environments, implement follow-up additive migrations rather than rewriting existing ones.
4. Phase 2: Implement the staging refresh pipeline. Depends on Phase 1.
5. Add an IDMS staging load job that calls `IdmsCardDataService::AllIdmsCardData($offset, $limit)` in pages until `totalReturned` stops or the accumulated count reaches `totalCount`. The job should normalize each payload row through `App\Services\IdmsCardData\DataTransferObjects\IdmsCardData::fromArray()` and map DTO fields to staging columns.
6. Treat the upstream API as a full snapshot on each run: load all rows into `idms_card_data_staging`, mark inserted/updated rows with `load_status`, and remove staging rows not present in the latest snapshot. The safest implementation is a run-scoped refresh pattern: stage into a fresh working set using chunked upserts keyed by identity, then delete stale rows after the full fetch completes.
7. Add a console command to execute the staging refresh synchronously or dispatch it to the queue, mirroring the shape of `ProcessFileUploadCommand` and `MergeCardDataCommand`. Include options for `--queue`, `--limit`, and possibly `--truncate-first` or `--batch-size` only if needed for operations.
8. Phase 3: Implement the staging-to-final merge pipeline. Depends on Phase 2.
9. Add an IDMS merge job that reads `idms_card_data_staging` in chunks and matches into `idms_card_data` using this exact hierarchy: first `net_id + ucr_id + student_id` when all three are present; next `net_id + ucr_id`; next `net_id + student_id`; finally `ucr_id + student_id`. Rows that do not satisfy at least one of those combinations should be skipped and logged explicitly instead of guessed.
10. For matched rows, update only when one or more of these tracked fields differs: `ucr_id`, `iso`, `library_card_number`, `status1`, `class`, `year_in_school`, `issue_date`, `edit_date`, `photo_date`. Also update identifier fields if the selected matching rule permits filling a previously blank identifier safely. For unmatched rows, insert new records into `idms_card_data`.
11. Decide merge semantics for rows missing from staging: exclude deletion from this implementation unless the user later requests full mirroring of the final table. Current scope is insert/update-only per the request.
12. Add a console command for the merge job with the same synchronous vs queued execution option pattern as the existing merge command.
13. Phase 4: Make scheduling configurable. Depends on Phases 2 and 3.
14. Extend `app/Console/Kernel.php` to schedule the two new commands separately, using env/config-backed cron expressions or day/time settings rather than hard-coded weekdays. Recommended default: staging refresh on Monday/Wednesday/Friday overnight; final merge on Tuesday/Thursday/Saturday overnight, both with `withoutOverlapping()` and stable names.
15. Add configuration entries and environment variable documentation for the schedule, batch size, page size, and optional queue selection. Keep the defaults conservative and easy to override without code edits.
16. Phase 5: Add focused tests and operational docs. Depends on all earlier phases.
17. Add feature/unit coverage for: DTO-to-table mapping, paginated staging fetch behavior, fallback identity matching, selective-update detection, insert-only behavior for new rows, skip-and-log behavior for rows without a valid match key set, and stale staging row removal during full refresh.
18. Add or update developer documentation describing the two commands, expected schedule configuration, runtime behavior, and recommended execution order.

**Relevant files**
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/app/Services/IdmsCardData/IdmsCardDataService.php` — reuse `AllIdmsCardData()` as the upstream fetch boundary; likely add a lower-level array-returning helper if job code should avoid parsing HTTP JSON out of a `JsonResponse`.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/app/Services/IdmsCardData/DataTransferObjects/IdmsCardData.php` — reuse DTO normalization and expand key aliases only if the upstream payload needs more mappings.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/app/Console/Commands/MergeCardDataCommand.php` — template for a synchronous-or-queued merge command.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/app/Console/Commands/ProcessFileUploadCommand.php` — template for richer command UX, logging, and queue dispatch options.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/app/Jobs/MergeCardDataDeltas.php` — reference for chunked merge processing, transactions, and logging, though the IDMS merge should use the new identity hierarchy instead of the UCR composite key.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/app/Console/Kernel.php` — add configurable schedules for the two new commands.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/config/services.php` — existing IDMS service credentials and cache settings; leave as the API credential source.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/database/migrations/2026_07_23_062837_create_idms_card_data_table.php` — current final table schema to validate against merge/update requirements and possibly supplement with indexes via a new migration.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/database/migrations/2026_07_23_063932_create_idms_card_data_staging_table.php` — current staging schema to validate against full-refresh loading and indexing needs.
- `/home/sudo-joelv/dev-drive/my-codes/library-public-api/docs/Merge_Card_Data_Job_Documentation.md` — reference style and operational concepts for sync documentation.

**Verification**
1. Run the new migrations or additive index migrations in the local containerized app environment using the repo’s `a` alias where appropriate.
2. Execute the staging-refresh command against a small page size and confirm that multiple `AllIdmsCardData` pages populate `idms_card_data_staging` with expected counts, normalized dates, and `load_status` values.
3. Re-run the staging-refresh command against unchanged upstream data and verify idempotent results plus stale-row cleanup behavior.
4. Seed representative staging records and run the merge command to verify inserts for new rows and updates only when one of the tracked fields changes.
5. Add tests covering the four matching branches: full triple key, `net_id + ucr_id`, `net_id + student_id`, and `ucr_id + student_id`, plus a skipped row with insufficient identifiers.
6. Verify scheduler registration by inspecting `php artisan schedule:list` in the app environment and confirming config-driven cron values are applied.

**Decisions**
- Included: two separate console-command-driven sync stages, queue-capable jobs, config-driven schedules, full staging refresh semantics, insert/update-only final merge semantics, and explicit fallback key hierarchy.
- Excluded: deleting rows from `idms_card_data` when they disappear from staging, unless requested later.
- Merge identity decision: use `net_id + ucr_id + student_id` first; if incomplete, fall back to `net_id + ucr_id`, then `net_id + student_id`, then `ucr_id + student_id`.
- Scheduling decision: include recommended defaults, but expose them through env/config so operations can change cadence without code changes.

**Further Considerations**
1. Recommended internal refactor: add a service method that returns raw normalized records and metadata rather than a `JsonResponse`, so jobs can consume the upstream API directly without unpacking controller-shaped responses.
2. If upstream records can legitimately change identity fields in a way that moves a row from one fallback branch to another, consider storing a deterministic source fingerprint in staging to improve stale-row cleanup and auditability.
3. If the dataset is large enough to make direct staging-table refresh risky, use a load-run identifier or temporary table strategy so stale-row deletion only occurs after a fully successful fetch.