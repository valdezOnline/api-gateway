# MergeCardDataDeltas Job Tests

This directory contains comprehensive unit and feature tests for the `MergeCardDataDeltas` job class, which is responsible for merging data from the `ucr_card_data_staging` table to the `ucr_card_data_actual` table.

## Test Files Overview

### 1. Unit Tests

#### `MergeCardDataDeltasTest.php`
This file contains comprehensive unit tests that focus on testing individual methods and core functionality of the job:

- **Delta Analysis Testing**: Tests the `analyzeTableDeltas()` method to ensure proper analysis of differences between staging and actual tables
- **Record Processing**: Tests insertion of new records and updating of existing records
- **Timestamp Precedence**: Verifies that only records with newer timestamps are processed
- **Data Preparation**: Tests the `prepareRecordData()` method for proper data formatting
- **Batch Processing**: Verifies that large datasets are processed efficiently in batches
- **Error Handling**: Tests database transaction rollback capabilities
- **Mixed Operations**: Tests scenarios with both inserts and updates in the same run

#### `MergeCardDataDeltasFactoryTest.php`
This file contains unit tests that use Laravel factories for more maintainable and realistic test data:

- **Student Lifecycle Changes**: Tests realistic scenarios like student graduation transitions
- **Faculty Card Replacements**: Tests card replacement scenarios for faculty members
- **Bulk Operations**: Tests processing of multiple new students
- **Mixed Operations**: Tests combinations of updates and inserts
- **Timestamp Precedence**: Verifies proper handling of newer vs older records
- **International Students**: Tests handling of international student records with different patterns
- **Status Transitions**: Tests realistic status changes (active to inactive, retirement, etc.)
- **Data Integrity**: Tests complex scenarios with overlapping identifiers

### 2. Feature Tests

#### `MergeCardDataDeltasFeatureTest.php`
This file contains feature tests that test the entire job workflow from start to finish:

- **Queue Integration**: Tests that the job can be properly dispatched to the queue
- **Full Workflow**: Tests complete merge operations with realistic university data
- **Large Dataset Processing**: Performance tests with hundreds of records
- **Concurrent Operations**: Tests data integrity during complex operations
- **Edge Cases**: Tests handling of null values, empty strings, and data anomalies
- **Real-World Patterns**: Tests scenarios that mimic actual university card system data
- **Timeout Handling**: Verifies job timeout configuration
- **Comprehensive Logging**: Tests that proper logging occurs during execution

## Test Data Factories

### `UcrCardDataStagingFactory.php`
Factory for creating test data for the staging table with:
- Realistic student, faculty, and staff profiles
- Various load statuses (created, updated, full-load)
- Configurable timestamps (older, newer)
- Specific identifiers and matching records

### `UcrCardDataActualFactory.php`
Factory for creating test data for the actual table with:
- Similar profiles as staging factory
- Active/inactive status configurations
- Matching staging record capabilities

## Running the Tests

### Run All Job Tests
```bash
php artisan test tests/Unit/Jobs/MergeCardDataDeltasTest.php
php artisan test tests/Feature/Jobs/MergeCardDataDeltasFeatureTest.php
php artisan test tests/Unit/Jobs/MergeCardDataDeltasFactoryTest.php
```

### Run Specific Test Categories
```bash
# Unit tests only
php artisan test tests/Unit/Jobs/

# Feature tests only
php artisan test tests/Feature/Jobs/

# All tests
php artisan test
```

### Run Tests with Coverage
```bash
php artisan test --coverage
```

## Test Database Setup

The tests use the RefreshDatabase trait, which ensures:
- Clean database state for each test
- Automatic table truncation between tests
- Proper transaction rollback after each test

Make sure your test database configuration is properly set up in `phpunit.xml` or your `.env.testing` file.

## Key Testing Scenarios Covered

### 1. Data Synchronization
- New records in staging are inserted into actual
- Updated records in staging update corresponding actual records
- Records with older timestamps are skipped

### 2. University-Specific Workflows
- Student transitions (undergraduate to graduate)
- Faculty card replacements
- Staff status changes
- International student processing

### 3. Data Integrity
- Unique constraint handling
- Null value processing
- Empty string handling
- Timestamp precedence

### 4. Performance and Scalability
- Batch processing of large datasets
- Memory management during bulk operations
- Processing time optimization

### 5. Error Handling
- Database connection failures
- Transaction rollback scenarios
- Data validation errors

## Mock Usage

The tests use Mockery for mocking Laravel's Log facade to:
- Verify proper logging occurs
- Test different log levels (info, error, warning)
- Ensure log messages contain expected content

## Best Practices Demonstrated

1. **Arrange-Act-Assert Pattern**: All tests follow the AAA pattern for clarity
2. **Descriptive Test Names**: Test method names clearly describe what is being tested
3. **Factory Usage**: Factories are used for maintainable and realistic test data
4. **Edge Case Coverage**: Tests cover both happy path and edge case scenarios
5. **Performance Testing**: Large dataset tests ensure the job scales properly
6. **Real-World Scenarios**: Tests mirror actual university card system usage patterns

## Maintenance Notes

- Update factories if database schema changes
- Add new tests when job functionality is extended
- Keep test data realistic and representative of actual usage
- Monitor test performance and optimize as needed
