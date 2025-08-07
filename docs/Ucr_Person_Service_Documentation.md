# UcrPerson Service Documentation

## Overview

The UcrPerson service is part of a Laravel-based library public API that provides access to UCR (University of California, Riverside) person data. This service integrates with the UCR Gateway API to retrieve and manage person information including students, employees, and other university affiliates.

## File Structure

```
app/Services/UcrPerson/
├── UcrPersonService.php
└── DataTransferObjects/
    └── UcrPersonData.php

Related Files:
├── app/Providers/UcrPersonServiceProvider.php
├── app/Http/Controllers/Api/v1/UcrPersonController.php
└── routes/api_v1.php (relevant routes)
```

## Core Files Documentation

### 1. UcrPersonService.php

**Location:** `app/Services/UcrPerson/UcrPersonService.php`

**Purpose:** Main service class that handles communication with the UCR Gateway API to retrieve person data.

**Key Features:**
- API integration with UCR Gateway
- Caching for performance optimization
- Support for multiple search methods
- Error handling and response formatting

#### Methods

##### `ucrPerson(string $stringId)`
```php
public function ucrPerson(string $stringId)
```
- **Purpose**: Retrieves a single person by ID (NetID or Employee ID)
- **Parameters**: 
  - `$stringId` (string) - NetID or Employee ID
- **Returns**: Formatted response with person data
- **Features**:
  - Uses intelligent routing based on ID format
  - Implements caching with configurable TTL
  - Returns formatted response with person data

##### `ucrPersonSearch(string $searchField, string $searchTerm)`
```php
public function ucrPersonSearch(string $searchField, string $searchTerm)
```
- **Purpose**: Performs search across person records
- **Parameters**:
  - `$searchField` (string) - Field to search in
  - `$searchTerm` (string) - Term to search for
- **Returns**: Collection of matching persons
- **Features**:
  - Supports multiple search fields
  - Returns collection of matching persons
  - Cached results for performance

#### Dependencies
- `ApiResponses` trait for consistent API responses
- Laravel's HTTP client for external API calls
- Laravel's Cache facade for data caching
- `UcrPersonData` DTO for data transformation

#### Example Usage
```php
// Retrieve person by NetID
$person = $ucrPersonService->ucrPerson('jdoe001');

// Search by last name
$results = $ucrPersonService->ucrPersonSearch('lastName', 'Smith');
```

### 2. UcrPersonData.php

**Location:** `app/Services/UcrPerson/DataTransferObjects/UcrPersonData.php`

**Purpose:** Data Transfer Object that standardizes person data structure and provides transformation methods.

#### Properties
- `netId`: University network identifier
- `displayName`: Full formatted name
- `emailAddress`: Primary email address
- `eduPersonPrimaryAffiliation`: Primary university affiliation
- `eduPersonAffiliation`: All university affiliations
- `studentId`: Student identifier (if applicable)
- `employeeId`: Employee identifier (if applicable)
- `firstName`, `middleName`, `lastName`: Name components
- `phoneNumber`: Contact phone number
- `ucrOrg`: UCR organizational unit
- `ou`: Organizational unit
- `title`: Job title or academic title
- `homeDepartmentCode`: Department code
- `homeDepartment`: Department name
- `ucrEnrollmentStatus`: Student enrollment status
- `ucrCollege`: Academic college
- `ucrClassStanding`: Academic standing
- `ucrLastRegistered`: Last registration date
- `separationDate`: Employment/enrollment end date
- `isActive`: Active status flag

#### Methods

##### `fromArray(array $data): self`
```php
public static function fromArray(array $data): self
```
- **Purpose**: Creates instance from API response array
- **Parameters**: `$data` (array) - Raw API response data
- **Returns**: UcrPersonData instance
- **Features**:
  - Handles data sanitization with `addslashes()`
  - Uses Laravel's `data_get()` helper for safe array access
  - Provides default empty strings for missing data

##### `fromCollection(array $data): Collection`
```php
public static function fromCollection(array $data): Collection
```
- **Purpose**: Transforms array of person records into Collection
- **Parameters**: `$data` (array) - Array of person records
- **Returns**: Laravel Collection of UcrPersonData instances
- **Features**:
  - Iterates through multiple records
  - Returns Laravel Collection of UcrPersonData instances

#### Example Usage
```php
// Create from API response
$personData = UcrPersonData::fromArray($apiResponse);

// Create collection from multiple records
$personsCollection = UcrPersonData::fromCollection($apiResponseArray);
```

### 3. UcrPersonServiceProvider.php

**Location:** `app/Providers/UcrPersonServiceProvider.php`

**Purpose:** Laravel service provider that registers the UcrPersonService as a singleton in the IoC container.

#### Configuration Features
- Binds service configuration from `config/services.php`
- Manages API credentials and endpoints
- Sets caching duration parameters

#### Singleton Registration
Registered as singleton to:
- Maintain consistent configuration
- Optimize performance
- Ensure proper dependency injection

### 4. UcrPersonController.php

**Location:** `app/Http/Controllers/Api/v1/UcrPersonController.php`

**Purpose:** API controller that exposes UcrPerson service functionality through HTTP endpoints.

#### Methods

##### `person(UcrPersonService $ucrPersonService, Request $request)`
```php
public function person(UcrPersonService $ucrPersonService, Request $request)
```
- **Purpose**: Handles GET requests for individual person lookup
- **Route**: `/api/v1/ucr-person/{stringId}`
- **Parameters**: 
  - `$stringId` (string) - NetID or Employee ID from route
- **Returns**: Person data or 404 if not found

##### `personSearch(UcrPersonService $ucrPersonService, Request $request)`
```php
public function personSearch(UcrPersonService $ucrPersonService, Request $request)
```
- **Purpose**: Handles GET requests for person search
- **Route**: `/api/v1/ucr-person/{searchField}/{searchTerm}`
- **Parameters**:
  - `$searchField` (string) - Field to search in
  - `$searchTerm` (string) - Search term
- **Returns**: Collection of matching persons

#### Dependencies
- Uses `ApiResponses` trait for consistent responses
- Leverages Laravel's dependency injection for service access

## Configuration

The service configuration is managed in `config/services.php`:

```php
'ucrPerson' => [
    'key' => env("UCRGW_API_KEY"),
    'baseUrl' => env("UCRGW_API_BASEURL"),
    'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
    'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
],
```

### Environment Variables
- `UCRGW_API_KEY`: API key for UCR Gateway authentication
- `UCRGW_API_BASEURL`: Base URL for UCR Gateway API
- `SINGLE_DATA_MINUTES`: Cache TTL for individual person lookups
- `MULTI_DATA_MINUTES`: Cache TTL for search results

## API Routes

Defined in `routes/api_v1.php`:

```php
Route::get('/ucr-person/{stringId}', [UcrPersonController::class, 'person']);
Route::get('/ucr-person/{searchField}/{searchTerm}', [UcrPersonController::class, 'personSearch']);
```

## Usage Examples

### API Endpoints

#### Retrieving a Person by NetID
```http
GET /api/v1/ucr-person/jdoe001
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "netId": "jdoe001",
        "displayName": "John Doe",
        "emailAddress": "john.doe@ucr.edu",
        "eduPersonPrimaryAffiliation": "student",
        "firstName": "John",
        "lastName": "Doe",
        "studentId": "862123456",
        "ucrCollege": "College of Engineering",
        "isActive": true
    }
}
```

#### Retrieving a Person by Employee ID
```http
GET /api/v1/ucr-person/1012345
```

#### Searching by Last Name
```http
GET /api/v1/ucr-person/lastName/Smith
```

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "netId": "asmith001",
            "displayName": "Alice Smith",
            "emailAddress": "alice.smith@ucr.edu",
            "eduPersonPrimaryAffiliation": "faculty",
            "firstName": "Alice",
            "lastName": "Smith",
            "employeeId": "1012346",
            "isActive": true
        },
        {
            "netId": "bsmith002",
            "displayName": "Bob Smith",
            "emailAddress": "bob.smith@ucr.edu",
            "eduPersonPrimaryAffiliation": "staff",
            "firstName": "Bob",
            "lastName": "Smith",
            "employeeId": "1012347",
            "isActive": true
        }
    ]
}
```

### Service Usage in Code

```php
// Inject service in controller
public function someMethod(UcrPersonService $ucrPersonService)
{
    // Get single person
    $person = $ucrPersonService->ucrPerson('jdoe001');
    
    // Search for persons
    $results = $ucrPersonService->ucrPersonSearch('lastName', 'Smith');
}
```

## Data Flow

1. **Request Reception**: Controller receives HTTP request
2. **Service Invocation**: Controller calls appropriate service method
3. **Cache Check**: Service checks for cached data
4. **API Call**: If cache miss, service calls UCR Gateway API
5. **Data Transformation**: Raw API response transformed to UcrPersonData DTO
6. **Caching**: Transformed data cached for future requests
7. **Response**: Formatted response returned to client

## Error Handling

The service implements comprehensive error handling for:

### API Communication Errors
- Network timeouts
- Connection failures
- Invalid API responses
- Authentication failures

### Data Validation Errors
- Malformed API responses
- Missing required fields
- Invalid data types

### Caching Errors
- Cache server unavailability
- Cache corruption
- Cache expiration handling

### Example Error Response
```json
{
    "status": "error",
    "message": "Person not found",
    "code": 404
}
```

## Security Considerations

### API Authentication
- API key authentication for UCR Gateway
- Secure storage of credentials in environment variables
- API key rotation capabilities

### Data Sanitization
- Input sanitization with `addslashes()`
- Safe array access with `data_get()` helper
- XSS prevention in response data

### Access Control
- Request validation through controllers
- Rate limiting through caching strategy
- IP whitelisting capabilities

### Data Privacy
- No storage of sensitive personal data
- Temporary caching only
- Compliance with university data policies

## Performance Optimizations

### Caching Strategy
- **Different TTL**: Separate cache durations for single vs. multi-record requests
- **Cache Keys**: Intelligent cache key generation
- **Cache Invalidation**: Automatic cache cleanup

### API Efficiency
- **Intelligent ID Routing**: Automatic detection of NetID vs. Employee ID
- **Connection Pooling**: Efficient HTTP client usage
- **Request Batching**: Optimized for multiple requests

### Data Processing
- **Lazy Loading**: Data only fetched when requested
- **Memory Management**: Efficient data transformation
- **Response Optimization**: Minimal data transfer

## Integration Points

The UcrPerson service integrates with:

### Student Services
- `SisActiveStudentService` - Student enrollment data
- Academic records and course information
- Student status and registration data

### Employee Services
- `HrEmployeeDetailService` - Employee information
- Organizational hierarchy data
- Employment status and benefits

### Library Services
- `ExLibrisAlmaPatronDataService` - Library patron data
- Circulation privileges
- Library account information

### Authentication Services
- Campus authentication systems
- Single sign-on integration
- Access control systems

## Testing

### Unit Testing Structure
```php
class UcrPersonServiceTest extends TestCase
{
    public function test_can_retrieve_person_by_netid()
    {
        // Mock API response
        Http::fake([
            'ucr-gateway.edu/*' => Http::response(['person_data' => [...]], 200)
        ]);
        
        $service = app(UcrPersonService::class);
        $result = $service->ucrPerson('jdoe001');
        
        $this->assertEquals('success', $result['status']);
    }
    
    public function test_caches_person_data()
    {
        // Test caching behavior
        Cache::shouldReceive('remember')->once();
        
        $service = app(UcrPersonService::class);
        $service->ucrPerson('jdoe001');
    }
}
```

### Integration Testing
- Test API connectivity
- Validate data transformation
- Test error scenarios
- Verify caching behavior

### Performance Testing
- Load testing with multiple requests
- Cache hit/miss ratio testing
- Response time measurements
- Memory usage monitoring

## Monitoring and Logging

### Key Metrics
- API response times
- Cache hit rates
- Error frequencies
- Request volumes

### Logging Strategy
- API call logging
- Error tracking
- Performance metrics
- Security events

### Alerting
- API downtime alerts
- Performance degradation warnings
- Error rate thresholds
- Cache failure notifications

## Maintenance

### Regular Tasks
- Monitor API rate limits
- Review cache performance
- Update API credentials
- Performance optimization

### Updates and Changes
- UCR Gateway API version updates
- Data mapping modifications
- Cache TTL adjustments
- Security patches

### Documentation Updates
- API endpoint changes
- New data fields
- Configuration updates
- Integration modifications

## Troubleshooting

### Common Issues

#### API Connection Failures
```php
// Check API configuration
config('services.ucrPerson.baseUrl')
config('services.ucrPerson.key')
```

#### Cache Issues
```php
// Clear cache manually
Cache::forget('ucr_person_jdoe001');
Cache::flush(); // Clear all cache
```

#### Data Transformation Errors
- Verify API response format
- Check data mapping in UcrPersonData
- Validate field names and types

### Debug Mode
Enable debug logging in `.env`:
```env
LOG_LEVEL=debug
APP_DEBUG=true
```

## Future Enhancements

### Planned Features
- Real-time data synchronization
- Advanced search capabilities
- Bulk data operations
- Enhanced caching strategies

### Performance Improvements
- Database caching layer
- API response compression
- Request optimization
- Background data refresh

### Security Enhancements
- Enhanced authentication
- Data encryption
- Audit logging
- Access monitoring

This comprehensive documentation provides all necessary information for understanding, using, maintaining, and extending the UcrPerson service within your library public API system.