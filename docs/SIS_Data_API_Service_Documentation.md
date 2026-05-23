# SIS Data API Service Documentation

This documentation covers the SisData service group and related API endpoints in the Laravel application.

## Overview

The SisData API service group provides a unified integration layer for Student Information System (SIS) data used by the library API. It currently includes:

- Active student profile retrieval
- Student person lookup by NetID or SID
- Term student lookup via criteria-based filtering

These services are registered in the container and exposed via authenticated API routes in the v1 API namespace.

## Core Components

### Service Classes

#### App\Services\SisData\ActiveStudentService

Purpose:
- Fetch active student records from SIS endpoints
- Enrich active student records with person and term data
- Provide single-record, paginated list, and count operations

Key methods:
- activeStudent(string stringId)
  - Calls SIS active-student endpoint
  - Extracts studentId from response
  - Enriches response with:
    - SisStudentPersonService::searchStudentPerson(studentId)
    - TermStudentService::SearchCriteria("isCurrentTerm=Y&enrolledThisTerm=Y&studentId={studentId}")
  - Returns ActiveStudentData DTO wrapped by ApiResponses

- activeStudents(Request request)
  - Calls active-student list endpoint with limit and offset
  - Returns mapped ActiveStudentData DTO collection
  - Uses cache key pattern:
    - activeStudents_limit_{limit}_offset_{offset}

- activeStudentCount()
  - Calls active-student count endpoint

Caching behavior:
- Multi-record caching uses multiDataMinutes
- Single-record caching in activeStudent is currently disabled (commented in source)

#### App\Services\SisData\SisStudentPersonService

Purpose:
- Fetch SIS person details by NetID or SID

Key method:
- searchStudentPerson(string stringId)
  - Calls endpoint: /api/sis-data-api/v1/ethos/persons/net-id-or-sid?id={stringId}
  - Maps to SisStudentPersonData DTO
  - Uses cache key pattern:
    - sisStudentPersonSearch_{stringId}

#### App\Services\SisData\TermStudentService

Purpose:
- Build SIS x-students criteria payloads from query params
- Retrieve term student records by dynamic criteria

Key method:
- SearchCriteria(string queryString)
  - Supports criteria patterns:
    - netId with optional termCode
    - studentId with optional isCurrentTerm and enrolledThisTerm
  - Encodes criteria and calls endpoint:
    - /api/sis-data-api/v1/ethos/x-students?criteria={urlEncodedCriteria}
  - Maps each record to TermStudentData DTO
  - Uses cache key pattern:
    - termStudent_{stringCriteria}

#### App\Services\SisData\BaseSisDataService (abstract)

Purpose:
- Shared safe request pattern, cache-aware request wrapper, DTO processing helpers, validation helpers

Current usage:
- Present as reusable base class for SisData patterns
- Not currently extended by ActiveStudentService, SisStudentPersonService, or TermStudentService

### Data Transfer Objects

#### App\Services\SisData\DataTransferObjects\ActiveStudentData

Highlights:
- Normalizes active student payload
- Includes enriched nested fields:
  - studentPersonData
  - termStudentData
- Includes helper methods:
  - isValid(), getFullName(), getFormattedAddress(), hasAddress(), hasContactInfo()

#### App\Services\SisData\DataTransferObjects\SisStudentPersonData

Highlights:
- Normalizes identity and contact data
- Extracts:
  - studentId (bannerId credential)
  - netId (bannerUserName credential)
  - personal and campus email
  - mobile and emergency phone
  - preferred full name

#### App\Services\SisData\DataTransferObjects\TermStudentData

Highlights:
- Normalizes term status, academic details, and program metadata
- Includes helper methods:
  - isValid(), isEnrolled(), hasGraduated(), isCurrentTerm(), getFullName(), getGpaAsFloat()

## API Controllers

### App\Http\Controllers\Api\v1\ActiveStudentController

Methods:
- activeStudent()
- activeStudents()
- activeStudentCount()

Backed by App\Services\SisData\ActiveStudentService.

### App\Http\Controllers\Api\v1\SisStudentPersonController

Method:
- searchStudentPerson()

Backed by App\Services\SisData\SisStudentPersonService.

### App\Http\Controllers\Api\v1\TermStudentController

Method:
- searchCriteria()

Backed by App\Services\SisData\TermStudentService.

## Service Providers

The SisData service classes are registered as singletons via:

- App\Providers\ActiveStudentServiceProvider
- App\Providers\SisStudentPersonServiceProvider
- App\Providers\TermStudentServiceProvider

Each provider binds from services.sisData configuration values.

## Configuration

Defined in config/services.php:

```php
'sisData' => [
    'key' => env("UCRGW_API_KEY"),
    'baseUrl' => env("UCRGW_API_BASEURL"),
    'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
    'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
]
```

Required environment variables:

```env
UCRGW_API_KEY=your_api_key
UCRGW_API_BASEURL=https://your-host
SINGLE_DATA_MINUTES=60
MULTI_DATA_MINUTES=30
```

## API Routes

Defined in routes/api_v1.php (auth:sanctum protected):

- GET /api/v1/sis/active-student/{stringId}
- GET /api/v1/sis/active-students
- GET /api/v1/sis/active-students-count
- GET /api/v1/sis/term-student
- GET /api/v1/sis/student-person/{stringId}

Also present:
- GET /api/v1/sis/active-students/{stringId}
  - This route maps to SisActiveStudentController (legacy/parallel SIS active-student service namespace)

## Request Examples

### Get one active student (enriched)

```http
GET /api/v1/sis/active-student/86123456
Authorization: Bearer {token}
```

### Get active student list

```http
GET /api/v1/sis/active-students?limit=100&offset=0
Authorization: Bearer {token}
```

### Get active student count

```http
GET /api/v1/sis/active-students-count
Authorization: Bearer {token}
```

### Get student person by NetID or SID

```http
GET /api/v1/sis/student-person/86123456
Authorization: Bearer {token}
```

### Get term student by criteria

Supported query combinations:
- netId with optional termCode
- studentId with optional isCurrentTerm and enrolledThisTerm

Examples:

```http
GET /api/v1/sis/term-student?netId=jdoe001
GET /api/v1/sis/term-student?netId=jdoe001&termCode=202440
GET /api/v1/sis/term-student?studentId=86123456&isCurrentTerm=Y
GET /api/v1/sis/term-student?studentId=86123456&isCurrentTerm=Y&enrolledThisTerm=Y
Authorization: Bearer {token}
```

## Response Envelope

SisData services use App\Traits\ApiResponses.

Current response envelope:

```json
{
  "message": "Success",
  "status": "success",
  "status_code": 200,
  "data": {},
  "count": 0
}
```

Notes:
- status is semantic (success or error)
- status_code is numeric
- count appears when data is an array

## Caching Strategy

- Single-record lookups use singleDataMinutes
- List/multi operations use multiDataMinutes
- Cache keys include request identity or criteria

Common key patterns:
- activeStudents_limit_{limit}_offset_{offset}
- sisStudentPersonSearch_{stringId}
- termStudent_{stringCriteria}

## Error Handling

Service-layer behavior includes:
- Validation of required search input
- External HTTP status passthrough where applicable
- Exception logging and standardized 500 responses

Controller-layer behavior includes:
- Guard rails for missing route/query inputs
- Early 400 responses when required request values are absent

## Integration Notes

ActiveStudentService performs enrichment by composing other SisData services at runtime:

1. Fetch active student
2. Derive studentId
3. Fetch student person details
4. Fetch current/enrolled term details
5. Return merged ActiveStudentData

This provides a single client call path for consolidated SIS student identity, contact, and enrollment context.

## Security

- All SisData API routes are protected by auth:sanctum
- External SIS requests require configured UCR gateway API key
- Response and request traces are logged for diagnostics

## Troubleshooting

Common issues:

1. 401 Unauthorized
- Confirm valid Sanctum token is sent

2. 400 Bad Request on term-student or student-person
- Confirm required query/route parameters are present

3. Empty or partial enrichment in active-student
- Validate studentId exists in active student source payload
- Verify SIS person and term endpoints for that studentId

4. Unexpected stale responses
- Review cache durations and clear cache if needed:
  - php artisan cache:clear

---

Last Updated: May 22, 2026
Version: 1.0
Maintainer: Library Public API Team
