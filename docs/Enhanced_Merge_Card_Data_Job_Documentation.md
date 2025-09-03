# Enhanced Merge Card Data Job Documentation

## Overview

The `MergeCardData` job has been enhanced to provide more intelligent data synchronization between the `ucr_card_data_staging` and `ucr_card_data_actual` tables. The enhanced version includes date-based filtering and timestamp comparison to ensure only relevant and newer data is processed.

## Key Features

### 1. Date-Based Filtering
- Only processes records from the staging table that have been created or updated within the last `nDays`
- The `nDays` value is configurable through the `.env` file using the `MERGE_CARD_DATA_DAYS` setting
- Default value is 7 days if not specified in the environment

### 2. Intelligent Update Logic
- Compares `created_at` and `updated_at` timestamps between staging and actual tables
- Only updates records in the actual table if the staging record is newer
- Skips updates for records where the actual table already has newer or equal data

### 3. Enhanced Logging
- Tracks and logs the number of records skipped (no update needed)
- Provides detailed information about the date range being processed
- Includes performance metrics and processing statistics

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
# Merge Card Data Configuration
# Number of days to look back for updated/created records in staging table
MERGE_CARD_DATA_DAYS="7"
```

### Customization Options

- **MERGE_CARD_DATA_DAYS**: Controls how far back to look for updated/created records
  - Recommended values: 1-30 days depending on your data update frequency
  - Lower values = faster processing but may miss some updates
  - Higher values = more comprehensive but slower processing

## Processing Logic

### 1. Date Range Calculation
```php
$nDays = (int) env('MERGE_CARD_DATA_DAYS', 7);
$cutoffDate = now()->subDays($nDays);
```

### 2. Staging Record Selection
Only processes records where:
- `created_at >= cutoffDate` OR
- `updated_at >= cutoffDate`

### 3. Update Decision Logic
For existing records in the actual table:
- Compare `updated_at` timestamps (falls back to `created_at` if `updated_at` is null)
- Only update if staging record timestamp > actual record timestamp
- Skip update if actual record is newer or equal

### 4. Insert Logic
- Insert new records that don't exist in the actual table
- All new records within the date range are inserted regardless of their timestamps

## Performance Considerations

### Memory Management
- Processes records in chunks of 1000 to handle large datasets
- Includes garbage collection to manage memory usage
- Transaction-based processing for data integrity

### Indexing Recommendations
Ensure the following indexes exist for optimal performance:

```sql
-- Staging table indexes
CREATE INDEX idx_ucr_card_staging_dates ON ucr_card_data_staging (created_at, updated_at);
CREATE INDEX idx_ucr_card_staging_identifiers ON ucr_card_data_staging (net_id, ssn, student_id);

-- Actual table indexes
CREATE INDEX idx_ucr_card_actual_dates ON ucr_card_data_actual (created_at, updated_at);
CREATE INDEX idx_ucr_card_actual_identifiers ON ucr_card_data_actual (net_id, ssn, student_id);
```

## Monitoring and Logging

### Log Messages
The job provides detailed logging including:
- Start and completion times
- Total records found within date range
- Processing progress every 1000 records
- Final statistics: inserted, updated, and skipped counts

### Sample Log Output
```
[INFO] Starting UCR Card Data merge process
[INFO] Processing records created or updated within the last 7 days (since 2025-07-31 12:00:00)
[INFO] Total staging records to process: 1500
[INFO] Inserted 200 new records
[INFO] Updated 150 existing records
[INFO] Processed 1000 records - Inserted: 200, Updated: 150, Skipped: 650
[INFO] UCR Card Data merge completed successfully
[INFO] Processing time: 45.67 seconds
[INFO] Total records processed: 1500
[INFO] Records inserted: 200
[INFO] Records updated: 150
[INFO] Records skipped (no update needed): 1150
```

## Error Handling

### Transaction Safety
- All operations are wrapped in a database transaction
- Automatic rollback on any errors
- Detailed error logging with stack traces

### Exception Handling
- Catches and logs all exceptions
- Re-throws exceptions to maintain job failure status
- Provides detailed error information for debugging

## Best Practices

### Scheduling
- Run during low-traffic periods to minimize database load
- Consider running multiple times per day for time-sensitive data
- Monitor processing times and adjust `MERGE_CARD_DATA_DAYS` as needed

### Data Integrity
- Regularly verify data consistency between staging and actual tables
- Monitor skip rates to ensure the logic is working as expected
- Consider implementing data validation checks

### Performance Optimization
- Monitor query performance and adjust indexes as needed
- Consider partitioning large tables by date
- Use appropriate batch sizes based on available memory and database performance

## Troubleshooting

### High Skip Rates
If you see many records being skipped:
- Check if the actual table is being updated by other processes
- Verify timestamp accuracy in both tables
- Consider adjusting the date range

### Slow Performance
If processing is slow:
- Check database indexes
- Monitor database load during execution
- Consider reducing batch size or date range
- Verify adequate server resources

### Data Inconsistencies
If data doesn't match expectations:
- Verify unique identifier logic (net_id, ssn, student_id)
- Check timestamp accuracy and timezone settings
- Review the prepareRecordData method for any data transformation issues
