# SIS Active Student Service Documentation

## Overview

The SIS Active Student service provides integration with the university's Student Information System (SIS) to retrieve active student data. This service is part of a larger library public API system that handles patron data management.

## Service Architecture

### Core Service Class

**`App\Services\SisActiveStudent\SisActiveStudentService`**

The main service class that handles API communication with the SIS system.

**Constructor Parameters:**
- `$key`: API authorization key for SIS endpoints
- `$baseUrl`: Base URL for the SIS API
- `$singleDataMinutes`: Cache duration for single record requests
- `$multiDataMinutes`: Cache duration for multi-record requests

**Key Methods:**

```php
public function sisActiveStudent(string $stringId)
```
Retrieves a single active student record by student ID. Results are cached based on the `singleDataMinutes` configuration.

```php
public function sisActiveStudents(Request $request)
```
Retrieves multiple active student records with pagination support via `limit` and `offset` parameters.

```php
public function activeStudentCount()
```
Returns the total count of active students in the system.

### Data Transfer Object

**`App\Services\SisActiveStudent\DataTransferObjects\SisActiveStudentData`**

A read-only data transfer object that encapsulates student information with strongly typed properties.

**Key Properties:**
- **Identity**: `personGuid`, `studentId`, `netId`, `affiliationType`
- **Personal Info**: `firstName`, `middleName`, `lastName`, `campus`
- **Address**: `addressLine1`, `addressLine2`, `addressLine3`, `city`, `stateOrProvince`, `postalCode`, `country`
- **Contact**: `phoneNumber`, `emailAddress`
- **Status Flags**: `nameDefault`, `nameActive`, `addressDefault`, `addressActive`, `phoneDefault`, `phoneActive`, `emailDefault`, `emailActive`

**Factory Method:**
```php
public static function fromArray(array $data): self
```
Creates a new instance from API response data using Laravel's `data_get()` helper for safe array access.

### HTTP Controller

**`App\Http\Controllers\Api\v1\SisActiveStudentController`**

REST API controller that exposes SIS student data endpoints.

**Endpoints:**
- `activeStudent()`: Get single student by ID
- `activeStudentCount()`: Get total active student count  
- `activeStudents()`: Get paginated list of active students

### Service Provider

**`App\Providers\SisActiveStudentServiceProvider`**

Registers the SIS Active Student service as a singleton in the Laravel service container.

**Configuration Binding:**
```php
SisActiveStudentService::class => new SisActiveStudentService(
    config('services.ucrPerson.key'),
    config('services.ucrPerson.baseUrl'), 
    config('services.ucrPerson.singleDataMinutes'),
    config('services.ucrPerson.multiDataMinutes')
)
```

## Configuration

The service is configured through the `config/services.php` file:

```php
'sisActiveStudent' => [
    'key' => env("UCRGW_API_KEY"),
    'baseUrl' => env("UCRGW_API_BASEURL"),
    'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
    'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
],
```

**Note**: Currently using `ucrPerson` config keys in the service provider, which may need alignment.

## Integration Points

### Patron Data Creation

The SIS Active Student service integrates with the patron data creation system in `app/Console/Commands/CreatePatronData.php`:

```php
// When student ID starts with '86', fetch student data from SIS
if (Str::startsWith($card['student_id'], '86')) {
    $stringId = $card['student_id'];
    $sisData = self::invokeApi("/sis/active-students/$stringId");
    // Process student data for patron record creation
}
```

## Caching Strategy

The service implements a comprehensive caching strategy:

- **Single Records**: Cached with key pattern `sisActiveStudent_{$stringId}`
- **Multiple Records**: Cached with key pattern `sisActiveStudents_limit_{$limit}_offset_{$offset}`
- **Cache Duration**: Configurable via environment variables

## API Response Format

The service uses the `ApiResponses` trait for consistent response formatting:

- **Success**: `$this->ok('Success', $data)`
- **Error**: `$this->error($message, $statusCode)`

## Error Handling

Comprehensive error handling includes:
- HTTP request failures
- API endpoint errors (404, etc.)
- Exception catching with proper error response formatting

## Usage Examples

### Getting a Single Student
```php
$sisService = app(SisActiveStudentService::class);
$response = $sisService->sisActiveStudent('86123456');
```

### Getting Multiple Students
```php
$request = new Request(['limit' => 50, 'offset' => 0]);
$response = $sisService->sisActiveStudents($request);
```

## Related Services

This service works alongside other university data services:
- `UcrPersonService` - General person data
- `HrEmployeeDetailService` - Employee data  
- `ExLibrisAlmaPatronDataService` - Library patron data

## Dependencies

- **Laravel HTTP Client**: For API communication
- **Laravel Cache**: For response caching
- **Laravel Service Container**: For dependency injection
- **Custom Traits**: `ApiResponses` for consistent response formatting

## API Endpoints

### GET /api/v1/sis/active-students/{id}
Retrieve a single active student by ID.

**Parameters:**
- `id` (string): Student ID

**Response:**
```json
{
    "status": "success",
    "message": "Success",
    "data": {
        "personGuid": "uuid",
        "studentId": "86123456",
        "netId": "student123",
        "firstName": "John",
        "lastName": "Doe",
        // ... other student data
    }
}
```

### GET /api/v1/sis/active-students/count
Get the total count of active students.

**Response:**
```json
{
    "status": "success", 
    "message": "Success",
    "data": {
        "count": 15000
    }
}
```

### GET /api/v1/sis/active-students
Get paginated list of active students.

**Parameters:**
- `limit` (integer, optional): Number of records to return
- `offset` (integer, optional): Number of records to skip

**Response:**
```json
{
    "status": "success",
    "message": "Success", 
    "data": [
        {
            "personGuid": "uuid1",
            "studentId": "86123456",
            // ... student data
        },
        {
            "personGuid": "uuid2", 
            "studentId": "86123457",
            // ... student data
        }
    ]
}
```

## Environment Variables

Required environment variables:

```env
UCRGW_API_KEY=your_api_key_here
UCRGW_API_BASEURL=https://api.example.edu
SINGLE_DATA_MINUTES=60
MULTI_DATA_MINUTES=30
```

## Security Considerations

- API key authentication required for all SIS endpoints
- Sensitive student data is handled according to FERPA guidelines
- Caching includes appropriate TTL to balance performance and data freshness
- Input validation on all parameters

## Troubleshooting

### Common Issues

1. **API Key Issues**: Verify `UCRGW_API_KEY` is set correctly
2. **Cache Issues**: Clear cache using `php artisan cache:clear`
3. **Configuration Mismatch**: Ensure service provider uses correct config keys
4. **Network Issues**: Check `UCRGW_API_BASEURL` connectivity

### Logging

The service logs important events and errors. Check Laravel logs for:
- API request failures
- Cache hits/misses
- Authentication issues