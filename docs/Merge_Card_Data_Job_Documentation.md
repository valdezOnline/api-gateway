# UCR Card Data Synchronization Jobs Documentation

## Overview

This document covers three different approaches for synchronizing data between the `ucr_card_data_staging` and `ucr_card_data_actual` tables. Each approach serves different use cases and offers unique benefits for data synchronization in the library public API system.

## Available Synchronization Jobs

### 1. MergeCardDataDeltas (Enhanced Delta Merge)
- **Purpose**: Intelligent delta-based synchronization with date filtering
- **Best for**: Regular incremental updates with timestamp comparison
- **Features**: Date-based filtering, timestamp comparison, skip logic

### 2. MirrorCardData (Complete Mirror)
- **Purpose**: Complete table replacement synchronization
- **Best for**: Full data refreshes and clean slate synchronization
- **Features**: Truncate and copy, simple and fast, guaranteed consistency

### 3. SmartMirrorCardData (Intelligent Mirror)
- **Purpose**: Surgical synchronization preserving referential integrity
- **Best for**: Systems with foreign key constraints and incremental updates
- **Features**: Delete missing, update changed, insert new, preserves existing data

## Job Selection Guide

### When to Use Each Job

**Use MergeCardDataDeltas when:**
- You need incremental updates based on timestamps
- You want to process only recent changes (configurable date range)
- You have high-frequency updates and want to minimize processing time
- You need detailed skip tracking for unchanged records

**Use MirrorCardData when:**
- You want a complete refresh of the actual table
- The staging table is the single source of truth
- Performance is critical for large datasets
- You don't have foreign key constraints pointing to the actual table

**Use SmartMirrorCardData when:**
- You have other tables with foreign keys pointing to the actual table
- You want to minimize data disruption during synchronization
- You need detailed tracking of deletions, insertions, and updates
- You're doing incremental updates with referential integrity preservation

## File Structure

```
app/Jobs/
├── MergeCardDataDeltas.php    # Enhanced delta merge with date filtering
├── MirrorCardData.php         # Complete table mirror (truncate and copy)
└── SmartMirrorCardData.php    # Intelligent mirror (delete/update/insert)

Related Models:
├── app/Models/UcrCardDataStaging.php
└── app/Models/UcrCardDataActual.php

Related Migrations:
├── database/migrations/2024_10_23_155150_create_ucr_card_data_table.php (staging)
└── database/migrations/2025_08_07_231421_create_table_ucr_card_data_actual.php (actual)
```

## Job Implementations

### 1. MergeCardDataDeltas (Enhanced Delta Merge)

#### Features
- **Date-Based Filtering**: Only processes records created/updated within configurable date range
- **Intelligent Update Logic**: Compares timestamps between staging and actual tables
- **Skip Logic**: Avoids unnecessary updates when actual table has newer data
- **Delta Analysis**: Analyzes table differences before processing

#### Configuration
Add to your `.env` file:
```env
# Merge Card Data Configuration
MERGE_CARD_DATA_DAYS="7"          # Days to look back for updates
FALLBACK_MERGE_CARD_DATA_DAYS="7" # Fallback if calculation fails
```

#### Processing Logic
1. **Delta Analysis**: Analyzes differences between tables
2. **Date Range Calculation**: Determines optimal processing window
3. **Record Selection**: Processes only records within date range
4. **Timestamp Comparison**: Updates only when staging is newer
5. **Batch Processing**: Handles large datasets efficiently

#### Sample Log Output
```
[INFO] Starting UCR Card Data merge process
[INFO] Analyze the deltas first.
[INFO] Table delta analysis completed: {"staging":{"count":15000},"actual":{"count":14800},...}
[INFO] Processing records created or updated within the last 7 days
[INFO] Total staging records to process: 1500
[INFO] Processed 1000 records - Inserted: 200, Updated: 150, Skipped: 650
[INFO] UCR Card Data merge completed successfully
[INFO] Records inserted: 200, Records updated: 150, Records skipped: 1150
```

### 2. MirrorCardData (Complete Mirror)

#### Features
- **Complete Replacement**: Truncates actual table and copies all staging data
- **Simple and Fast**: Minimal logic, maximum performance
- **Guaranteed Consistency**: Actual table becomes exact copy of staging
- **Verification**: Compares record counts after mirroring

#### Processing Logic
1. **Count Verification**: Checks staging table record count
2. **Table Truncation**: Clears actual table completely
3. **Bulk Copy**: Copies all data from staging in chunks
4. **Verification**: Ensures record counts match

#### Sample Log Output
```
[INFO] Starting UCR Card Data mirror process
[INFO] Records in staging table: 15000
[INFO] Records in actual table before mirror: 14800
[INFO] Truncating actual table
[INFO] Mirrored 15000 records so far
[INFO] UCR Card Data mirror completed successfully
[INFO] Records mirrored: 15000
[INFO] Mirror verification successful - record counts match
```

### 3. SmartMirrorCardData (Intelligent Mirror)

#### Features
- **Three-Phase Process**: Delete missing, update changed, insert new
- **Referential Integrity**: Preserves existing data relationships
- **Change Detection**: Only updates records that actually need updating
- **Comprehensive Tracking**: Detailed statistics for all operations

#### Processing Logic
1. **Delete Phase**: Removes records that exist in actual but not in staging
2. **Process Phase**: Iterates through all staging records
3. **Update Detection**: Compares all fields to determine if update needed
4. **Bulk Operations**: Performs inserts and updates in batches

#### Sample Log Output
```
[INFO] Starting UCR Card Data smart mirror process
[INFO] Initial counts - Staging: 15000, Actual: 14800
[INFO] Step 1: Removing records that no longer exist in staging
[INFO] Deleted 50 records not present in staging
[INFO] Step 2: Processing staging records for inserts and updates
[INFO] Inserted 200 new records
[INFO] Updated 150 existing records
[INFO] Smart mirror verification successful - record counts match
```

## Common Job Properties

All synchronization jobs share these characteristics:

- **Queue**: Default Laravel queue (configurable)
- **Timeout**: 3600 seconds (1 hour)
- **Implements**: `ShouldQueue` for asynchronous processing
- **Uses Traits**: `Dispatchable`, `InteractsWithQueue`, `Queueable`, `SerializesModels`
- **Batch Size**: 1000 records per chunk (optimizable)
- **Transaction Safety**: Full database transaction with rollback on errors
- **Memory Management**: Garbage collection and chunked processing
## Database Schema

### Source Table: `ucr_card_data_staging`
- Primary staging area for incoming card data
- Temporary storage before validation and merge

### Target Table: `ucr_card_data_actual`
- Production table containing validated card data
- Final destination for merged records

### Schema Fields
- `net_id`: University CAS identifier (part of composite key)
- `ssn`: Student/Employee/Visitor/Guest Number (part of composite key)
- `student_id`: Student/Employee identifier (part of composite key)
- `iso`: ISO card number
- `lib_num`: Library number
- `status1`: Current status
- `class`: Academic class
- `yr_in_school`: Year in school
- `stud_fac`: Student/Faculty indicator
- `prox_int`: Internal proximity data
- `prox_ext`: External proximity data
- `prox_status`: Proximity status
- `issued`: Card issue date
- `edit_date`: Last edit date
- `photo_date`: Photo date
- `imported`: Import date
- `load_status`: Load status indicator

## Usage

### Dispatching Jobs

```php
// Enhanced Delta Merge
MergeCardDataDeltas::dispatch();

// Complete Mirror
MirrorCardData::dispatch();

// Smart Mirror
SmartMirrorCardData::dispatch();

// With options
MirrorCardData::dispatch()
    ->delay(now()->addMinutes(5))
    ->onQueue('card-processing');
```

### Artisan Command Integration

```bash
# Create artisan command to trigger the job
php artisan make:command MergeCardDataCommand

# Run the job via command
php artisan app:merge-card-data
```

### Scheduled Execution

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Enhanced delta merge - frequent updates
    $schedule->job(new MergeCardDataDeltas())
             ->everyFiveMinutes()
             ->name('merge-card-data-deltas')
             ->withoutOverlapping();

    // Complete mirror - daily refresh
    $schedule->job(new MirrorCardData())
             ->dailyAt('02:00')
             ->name('mirror-card-data')
             ->withoutOverlapping();

    // Smart mirror - hourly sync
    $schedule->job(new SmartMirrorCardData())
             ->hourly()
             ->name('smart-mirror-card-data')
             ->withoutOverlapping();
}
```

## Performance Optimization

### Database Indexing

Ensure these indexes exist for optimal performance:

```sql
-- Staging table indexes
CREATE INDEX idx_ucr_card_staging_dates ON ucr_card_data_staging (created_at, updated_at);
CREATE INDEX idx_ucr_card_staging_identifiers ON ucr_card_data_staging (net_id, ssn, student_id);
CREATE INDEX idx_ucr_card_staging_load_status ON ucr_card_data_staging (load_status);

-- Actual table indexes
CREATE INDEX idx_ucr_card_actual_dates ON ucr_card_data_actual (created_at, updated_at);
CREATE INDEX idx_ucr_card_actual_identifiers ON ucr_card_data_actual (net_id, ssn, student_id);
```

### Memory Management
- **Chunked Processing**: All jobs handle large datasets efficiently
- **Garbage Collection**: Forces cleanup every 1000 records
- **Batch Size**: Configurable (default: 1000 records per chunk)
- **Transaction Batching**: Minimizes database lock time

### Performance Comparison

| Job Type            | Speed    | Memory Usage | Data Disruption | Use Case              |
| ------------------- | -------- | ------------ | --------------- | --------------------- |
| MergeCardDataDeltas | Fast     | Low          | Minimal         | Incremental updates   |
| MirrorCardData      | Fastest  | Low          | Complete        | Full refreshes        |
| SmartMirrorCardData | Moderate | Medium       | Minimal         | Referential integrity |

## Error Handling and Logging

### Transaction Safety
- **Automatic Rollback**: All jobs rollback on any failure
- **Data Consistency**: Maintains integrity during errors
- **Partial Update Prevention**: Ensures complete success or complete failure

### Comprehensive Logging

#### Success Logging (All Jobs)
```php
Log::info("Job completed successfully");
Log::info("Processing time: {$processingTime} seconds");
Log::info("Records processed: {$recordsProcessed}");
```

#### Job-Specific Logging

**MergeCardDataDeltas:**
```php
Log::info("Records inserted: {$recordsInserted}");
Log::info("Records updated: {$recordsUpdated}");
Log::info("Records skipped: {$recordsSkipped}");
```

**MirrorCardData:**
```php
Log::info("Records mirrored: {$recordsMirrored}");
Log::info("Mirror verification successful");
```

**SmartMirrorCardData:**
```php
Log::info("Records deleted: {$recordsDeleted}");
Log::info("Records inserted: {$recordsInserted}");
Log::info("Records updated: {$recordsUpdated}");
```

#### Error Logging (All Jobs)
```php
Log::error("Job failed: " . $exception->getMessage());
Log::error("Stack trace: " . $exception->getTraceAsString());
```

## Integration Points

### Related Services
- **ProcessFileUpload**: Populates staging table
- **CaptureFileUpload**: File validation and preparation
- **Card Data Controllers**: API endpoints for data access

### External Dependencies
- **Laravel Queue System**: For asynchronous processing
- **Database Transactions**: For data integrity
- **Logging System**: For monitoring and debugging

## Monitoring and Maintenance

### Key Metrics to Monitor

#### Performance Metrics
- Job execution time (target: < 30 minutes)
- Memory usage during processing
- Database connection pool usage
- Queue processing lag

#### Data Metrics
- Number of records processed per job
- Insert/update/delete ratios
- Skip rates (for MergeCardDataDeltas)
- Record count mismatches

#### Error Metrics
- Job failure rates
- Transaction rollback frequency
- Timeout occurrences
- Memory exhaustion events

### Alerting Recommendations

#### Critical Alerts
- Job failure notifications
- Processing time > 30 minutes
- Record count mismatches > 1%
- Memory usage > 80%

#### Warning Alerts
- Large dataset processing (> 100K records)
- High skip rates (> 50% for delta merge)
- Queue backlog > 10 jobs
- Database connection issues

### Regular Maintenance Tasks

#### Daily
- Review job execution logs
- Monitor processing times
- Check data consistency
- Validate queue health

#### Weekly
- Analyze performance trends
- Review error patterns
- Optimize database queries
- Update documentation

#### Monthly
- Performance benchmarking
- Database index optimization
- Capacity planning
- System resource review

## Testing Strategy

### Unit Tests

#### Common Test Cases (All Jobs)
```php
// Test basic job execution
public function test_job_executes_successfully()
public function test_job_handles_empty_staging_table()
public function test_job_rolls_back_on_exception()
public function test_batch_processing_works()
```

#### Job-Specific Tests

**MergeCardDataDeltas:**
```php
public function test_date_range_calculation()
public function test_delta_analysis()
public function test_skip_logic_for_older_records()
public function test_timestamp_comparison()
```

**MirrorCardData:**
```php
public function test_table_truncation()
public function test_complete_data_copy()
public function test_record_count_verification()
```

**SmartMirrorCardData:**
```php
public function test_delete_missing_records()
public function test_update_detection_logic()
public function test_referential_integrity_preservation()
```

### Integration Tests
- End-to-end synchronization process
- Database transaction behavior
- Queue job execution
- Large dataset handling
- Concurrent job execution

### Performance Tests
- Memory usage profiling
- Execution time benchmarks
- Database load testing
- Stress testing with large datasets

## Security Considerations

### Data Protection
- No sensitive data in logs
- Secure database connections
- Transaction isolation
- Access control compliance

### Audit Trail
- Complete operation logging
- Change tracking
- Error documentation
- Performance metrics

## Future Enhancements

### Planned Improvements
- Parallel processing capabilities
- Real-time sync options
- Enhanced error recovery
- Performance optimizations

### Configuration Options
- Batch size tuning
- Timeout adjustments
- Queue priority settings
- Logging level controls

## Troubleshooting

### Common Issues by Job Type

#### MergeCardDataDeltas Issues
1. **High Skip Rates**
   - **Cause**: Actual table being updated by other processes
   - **Solution**: Check for concurrent updates, verify timestamp accuracy
   - **Prevention**: Coordinate update schedules

2. **No Records Processed**
   - **Cause**: Date range too restrictive
   - **Solution**: Increase `MERGE_CARD_DATA_DAYS` value
   - **Prevention**: Monitor delta analysis output

#### MirrorCardData Issues
1. **Performance Degradation**
   - **Cause**: Large dataset causing memory issues
   - **Solution**: Reduce batch size, increase memory limits
   - **Prevention**: Monitor dataset growth

2. **Record Count Mismatches**
   - **Cause**: Concurrent staging table modifications
   - **Solution**: Ensure staging table stability during execution
   - **Prevention**: Schedule during low-activity periods

#### SmartMirrorCardData Issues
1. **Foreign Key Constraint Violations**
   - **Cause**: Dependent tables referencing deleted records
   - **Solution**: Update deletion logic to handle dependencies
   - **Prevention**: Analyze foreign key relationships

2. **Slow Performance**
   - **Cause**: Complex update detection logic
   - **Solution**: Optimize field comparison logic, add indexes
   - **Prevention**: Regular performance monitoring

### General Troubleshooting Steps

#### Performance Issues
1. Check database indexes
2. Monitor system resources during execution
3. Analyze query execution plans
4. Consider batch size optimization
5. Review concurrent database activity

#### Data Consistency Issues
1. Verify composite key uniqueness
2. Check timestamp accuracy and timezone settings
3. Review data transformation logic
4. Validate source data quality
5. Compare before/after record counts

#### Memory Issues
1. Enable garbage collection
2. Reduce batch size
3. Increase PHP memory limits
4. Monitor memory usage patterns
5. Consider chunking optimization

### Debugging Commands

```bash
# Check job queue status
php artisan queue:work --once

# Monitor job execution
php artisan queue:listen --verbose

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## Security and Compliance

### Data Protection
- **No Sensitive Data in Logs**: Personal information is excluded from log messages
- **Secure Database Connections**: Uses encrypted connections and proper authentication
- **Transaction Isolation**: Ensures data consistency during concurrent operations
- **Access Control Compliance**: Follows institutional data access policies

### Audit Trail
- **Complete Operation Logging**: All operations are logged with timestamps
- **Change Tracking**: Detailed tracking of insertions, updates, and deletions
- **Error Documentation**: Comprehensive error logging for compliance
- **Performance Metrics**: Processing statistics for audit purposes

## Integration Points

### Related Services
- **ProcessFileUpload**: Populates staging table with validated data
- **CaptureFileUpload**: File validation and preparation services
- **Card Data Controllers**: API endpoints for data access and management

### External Dependencies
- **Laravel Queue System**: For asynchronous job processing
- **Database Transactions**: For data integrity and consistency
- **Logging System**: For monitoring, debugging, and audit trails

## Future Enhancements

### Planned Improvements
- **Parallel Processing**: Multi-threaded execution for large datasets
- **Real-time Sync**: Event-driven synchronization capabilities
- **Enhanced Error Recovery**: Automatic retry with exponential backoff
- **Performance Optimizations**: Query optimization and caching strategies

### Configuration Expansion
- **Dynamic Batch Sizing**: Automatic optimization based on dataset size
- **Flexible Timeout Management**: Job-specific timeout configurations
- **Queue Priority Settings**: Priority-based job execution
- **Advanced Logging Controls**: Configurable log levels and destinations

This comprehensive documentation provides complete guidance for understanding, implementing, and maintaining all UCR Card Data synchronization jobs within the library public API system.
