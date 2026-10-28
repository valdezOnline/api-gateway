# IDMS Card Data Source Clarification

**Status:** ✅ Confirmed architecture direction
**Date:** 2026-08-27
**Area:** API source-of-truth for card data

## Problem Description

Recent PHP 8.5.12 validation identified failing API tests that appeared to indicate a compatibility issue. After review, the failure was due to the tests still seeding and asserting against the legacy `ucr_card_data_actual` source while the production controller is intentionally querying the current IDMS-backed source.

## Correct Source of Truth

The controller `app/Http/Controllers/Api/v1/UcrCardDataController.php` is correctly querying the `IdmsCardData` model/table, not the legacy `UcrCardDataActual` table.

This is aligned with the current architecture:

- File-based card data imports are being phased out.
- `UcrCardData` / `ucr_card_data_actual` is a backup/data-retention table.
- Card data is now sourced from the IDMS API via the service provider and scheduled command pipeline.

## Production Flow

The current pipeline is:

1. `App\Providers\IdmsCardDataServiceProvider`
   - Registers `IdmsCardDataService`
2. `app:load-idms-card-data-staging`
   - `LoadIdmsCardDataStagingCommand`
   - loads current IDMS data into `idms_card_data_staging`
3. `app:merge-idms-card-data`
   - `MergeIdmsCardDataCommand`
   - merges staged IDMS data into `idms_card_data`
4. `UcrCardDataController` reads from `IdmsCardData`
   - this is the active API source
5. `UcrCardDataActual` remains a fallback/backup if the IDMS provider is unavailable

## Why the tests were failing

The API tests under `tests/Feature/Api/v1/UcrCardDataApiTest.php` were creating records in `UcrCardDataActual` and then asserting controller behavior that reads from `IdmsCardData`.

That means the tests were validating against the deprecated/legacy source instead of the current active source. This does not indicate a PHP 8.5.12 runtime incompatibility; it indicates stale test assumptions.

## Files to Monitor

- `app/Http/Controllers/Api/v1/UcrCardDataController.php`
- `app/Models/IdmsCardData.php`
- `app/Providers/IdmsCardDataServiceProvider.php`
- `app/Console/Commands/LoadIdmsCardDataStagingCommand.php`
- `app/Console/Commands/MergeIdmsCardDataCommand.php`
- `tests/Feature/Api/v1/UcrCardDataApiTest.php`

## Verification Summary

- `UcrCardDataController` is intentionally aligned with the active IDMS-backed data model.
- `UcrCardDataActual` is legacy/backup data and should not be used as the source for current API assertions.
- The API test failures observed under PHP 8.5.12 are due to outdated test fixtures and data assumptions, not a runtime issue in PHP 8.5.12.

## Recommendation

Update the API tests to seed and validate `IdmsCardData` instead of `UcrCardDataActual` so they reflect the current production architecture. Keep `UcrCardDataActual` for backup/fallback scenarios only.
