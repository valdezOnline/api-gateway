# HR Employee Detail Service Documentation

This documentation covers the HR Employee Detail service and its related components in the Laravel application.

## Overview

The HR Employee Detail service provides functionality to retrieve and manage employee information from the UCR HR API. It includes data transfer objects, service classes, and API controllers for handling employee data operations.

## Core Components

### Service Class

#### [`HrEmployeeDetailService`](app/Services/HrEmployeeDetail/HrEmployeeDetailService.php)

The main service class that handles HR employee data operations with caching and API integration.

**Constructor Parameters:**
- `$key`: API authorization key
- `$baseUrl`: Base URL for HR API endpoints
- `$singleDataMinutes`: Cache duration for single employee requests
- `$multiDataMinutes`: Cache duration for multiple employee requests

**Key Methods:**

- **`getResponse($url)`**: Internal method for making API requests with error handling
- **`HrEmployeeDetail(string $netId)`**: Retrieves detailed employee information by NetID
- **`hrEmployeeDetails(Request $request)`**: Retrieves multiple employee details using comma-separated NetIDs
- **`hrEmployeeJob(Request $request)`**: Retrieves job information for a specific employee

**Caching Strategy:**
- Single employee data: `hrEmployee_{netId}`
- Multiple employee data: `hrEmployee_details_{netIds}`
- Job data: `hrJob_{netId}`

### Data Transfer Object

#### [`HrEmployeeData`](app/Services/HrEmployeeDetail/DataTransferObjects/HrEmployeeData.php)

A read-only data transfer object that structures HR employee data.

**Properties:**
```php
public readonly string $employeeId;
public readonly string $netId;
public readonly string $firstName;
public readonly string $middleName;
public readonly string $lastName;
public readonly string $phoneNumber;
public readonly string $emailAddress;
public readonly string $address1;
public readonly string $address2;
public readonly string $address3;
public readonly string $address4;
public readonly string $city;
public readonly string $state;
public readonly string $postCode;
public readonly string $countryCode;
public readonly string $employeeClassDesc;
public readonly string $employeeStatus;
public readonly string $jobCode;
public readonly string $jobCodeDescription;
public readonly string $supervisorNetId;
public readonly string $supervisorFullName;
public readonly string $departmentCode;
public readonly string $departmentCodeDescription;
```

**Key Methods:**

- **`fromArray(array $data): self`**: Creates a single employee data instance from API response
- **`fromCollection(array $data): Collection`**: Creates a collection of employee data instances

**Data Processing Logic:**
- Extracts preferred contact information (email, phone, address)
- Identifies primary job information
- Maps employee status codes ('A' = 'Active')
- Handles supervisor information extraction

### API Controller

#### [`HrEmployeeDetailController`](app/Http/Controllers/Api/v1/HrEmployeeDetailController.php)

REST API controller that exposes HR employee data endpoints.

**Endpoints:**
- **`hrEmployee()`**: Single employee lookup
- **`hrEmployeeDetails()`**: Multiple employee lookup
- **`hrEmployeeJob()`**: Employee job information

### Service Provider

#### [`HrEmployeeDetailServiceProvider`](app/Providers/HrEmployeeDetailServiceProvider.php)

Laravel service provider that registers the HR Employee Detail service as a singleton.

**Configuration Binding:**
```php
config('services.hrEmployeeDetail.key')
config('services.hrEmployeeDetail.baseUrl')
config('services.hrEmployeeDetail.singleDataMinutes')
config('services.hrEmployeeDetail.multiDataMinutes')
```

## Configuration

### Service Configuration

Located in [`config/services.php`](config/services.php):

```php
'hrEmployeeDetail' => [
    'key' => env("UCRGW_API_KEY"),
    'baseUrl' => env("UCRGW_API_BASEURL"),
    'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
    'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
],
```

### Service Provider Registration

In [`config/app.php`](config/app.php):
```php
App\Providers\HrEmployeeDetailServiceProvider::class,
```

## API Endpoints

### Routes

Defined in [`routes/api_v1.php`](routes/api_v1.php):

```php
Route::get('/hr/employee/{netId}', [HrEmployeeDetailController::class, 'hrEmployee']);
Route::get('/hr/employee-details/{netIds}', [HrEmployeeDetailController::class, 'hrEmployeeDetails']);
Route::get('/hr/employee-job/{netId}', [HrEmployeeDetailController::class, 'hrEmployeeJob']);
```

### Example Usage

**Single Employee:**
```
GET /api/v1/hr/employee/jsmith
```

**Multiple Employees:**
```
GET /api/v1/hr/employee-details/jsmith+kdoe+bwilson
```

**Employee Job Information:**
```
GET /api/v1/hr/employee-job/jsmith
```

## Data Processing Features

### Contact Information Extraction

The service intelligently extracts preferred contact information:

1. **Names**: Looks for 'PRF' (Preferred) type names
2. **Phone Numbers**: Identifies preferred phone numbers
3. **Email Addresses**: Finds preferred email addresses marked with 'Y'
4. **Addresses**: Extracts 'HOME' type addresses

### Job Information Processing

- Identifies primary job assignments (`isPrimary === true`)
- Extracts supervisor information (NetID and full name)
- Maps employee status codes to readable descriptions
- Retrieves department and job code information

### Data Sanitization

- Uses `addslashes()` for string fields to prevent SQL injection
- Replaces '/' characters with '-' in phone numbers
- Handles null/empty values with fallback to empty strings

## Error Handling

The service implements comprehensive error handling:

- **404 Errors**: When employee data is not found
- **500 Errors**: For general exceptions and API failures
- **Cache Flushing**: Automatic cache clearing on errors
- **Validation**: Empty data validation with appropriate error responses

## Integration Points

### Used by Other Services

The HR Employee Detail service is referenced in:

- [`CreatePatronData`](app/Console/Commands/CreatePatronData.php) command for patron data creation
- Cross-service employee verification and data enrichment

### Dependencies

- **Laravel HTTP Client**: For API communication
- **Laravel Cache**: For response caching
- **ApiResponses Trait**: For standardized API responses

## Performance Considerations

1. **Caching**: Implements intelligent caching with different durations for single vs. multiple requests
2. **Batch Processing**: Supports multiple employee lookup in a single request
3. **Memory Management**: Uses collections for efficient data handling
4. **Error Recovery**: Cache flushing on errors prevents stale data

## Security Features

- **API Key Authentication**: Secure API access using environment-based keys
- **Data Sanitization**: Input sanitization to prevent injection attacks
- **Rate Limiting**: Built-in caching reduces API call frequency
- **Error Masking**: Sensitive error information is not exposed to end users

## Testing Considerations

When testing this service, consider:

1. **Mock External API**: Use HTTP fakes for testing API interactions
2. **Cache Testing**: Test cache hit/miss scenarios
3. **Error Scenarios**: Test 404, 500, and network failure cases
4. **Data Validation**: Test edge cases with malformed API responses
5. **Performance Testing**: Test with large employee datasets

This service provides a robust foundation for HR employee data management within the library system, with proper error handling, caching, and data transformation capabilities.

---

**Last Updated:** August 5, 2025  
**Version:** 1.0  
**Maintainer:** Library Public API Team