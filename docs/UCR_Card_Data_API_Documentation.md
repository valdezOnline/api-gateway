# UCR Card Data API Documentation

## Overview

The UCR Card Data API provides access to university card data from the `ucr_card_data_actual` table. It supports various search methods and returns consistent JSON responses.

## Authentication

All endpoints require authentication using Laravel Sanctum tokens. Include the token in the Authorization header:

```
Authorization: Bearer {your_token}
```

## Base URL

```
/api/v1/ucr-card-data/
```

## Endpoints

### 1. List All Records (with Optional Search Parameters)

**GET** `/list`

Returns all UCR card data records with pagination support, or filtered results based on query parameters.

#### Query Parameters:
- `net_id` (string, optional) - Search by network ID (can return multiple records)
- `ssn` (string, optional) - Search by SSN (returns single record)
- `student_id` (string, optional) - Search by student ID (returns single record)
- `iso` (string, optional) - Search by ISO number (returns single record)
- `start_date` (string, optional) - Start date for date range search (YYYY-MM-DD)
- `end_date` (string, optional) - End date for date range search (YYYY-MM-DD)
- `per_page` (integer, optional) - Number of records per page (default: 50)

#### Examples:

```bash
# Get all records with pagination
GET /api/v1/ucr-card-data/list

# Search by net_id
GET /api/v1/ucr-card-data/list?net_id=jdoe001

# Search by SSN
GET /api/v1/ucr-card-data/list?ssn=123456789

# Search by date range
GET /api/v1/ucr-card-data/list?start_date=2024-01-01&end_date=2024-12-31

# Paginated results
GET /api/v1/ucr-card-data/list?per_page=100
```

#### Response Format:

```json
{
    "message": "UCR card data retrieved successfully",
    "status": "success",
    "data": [
        {
            "id": 1,
            "net_id": "jdoe001",
            "ssn": "123456789",
            "student_id": "862123456",
            "iso": "12345",
            "lib_num": "2123456789012345",
            "status1": "Active Student",
            "class": "U",
            "yr_in_school": "U3",
            "stud_fac": "S",
            "prox_int": "12345",
            "prox_ext": "67890",
            "prox_status": "A",
            "issued": "2024-01-15",
            "edit_date": "2024-01-16",
            "photo_date": "2024-01-14",
            "imported": "2024-01-17",
            "load_status": "active",
            "created_at": "2024-01-17T10:00:00.000000Z",
            "updated_at": "2024-01-17T10:00:00.000000Z"
        }
    ],
    "count": 1
}
```

### 2. Search by Net ID

**GET** `/net-id/{netId}`

Search for records by network ID. Can return multiple records.

#### Parameters:
- `netId` (string, required) - The network ID to search for

#### Example:

```bash
GET /api/v1/ucr-card-data/net-id/jdoe001
```

### 3. Search by SSN

**GET** `/ssn/{ssn}`

Search for a record by Social Security Number. Returns single record or 404.

#### Parameters:
- `ssn` (string, required) - The SSN to search for

#### Example:

```bash
GET /api/v1/ucr-card-data/ssn/123456789
```

### 4. Search by Student ID

**GET** `/student-id/{studentId}`

Search for a record by Student ID. Returns single record or 404.

#### Parameters:
- `studentId` (string, required) - The student ID to search for

#### Example:

```bash
GET /api/v1/ucr-card-data/student-id/862123456
```

### 5. Search by ISO

**GET** `/iso/{iso}`

Search for a record by ISO number. Returns single record or 404.

#### Parameters:
- `iso` (string, required) - The ISO number to search for

#### Example:

```bash
GET /api/v1/ucr-card-data/iso/12345
```

### 6. Search by Date Range

**POST** `/date-range`

Search for records issued within a specific date range.

#### Request Body:

```json
{
    "start_date": "2024-01-01",
    "end_date": "2024-12-31"
}
```

#### Validation Rules:
- `start_date` - Required, valid date in YYYY-MM-DD format
- `end_date` - Required, valid date in YYYY-MM-DD format, must be after or equal to start_date

#### Example:

```bash
POST /api/v1/ucr-card-data/date-range
Content-Type: application/json

{
    "start_date": "2024-01-01",
    "end_date": "2024-12-31"
}
```

## Response Codes

- `200` - Success
- `400` - Bad Request (invalid date format)
- `401` - Unauthorized (missing or invalid token)
- `404` - Not Found (no records match criteria)
- `422` - Validation Error (invalid request data)
- `500` - Internal Server Error

## Error Response Format

```json
{
    "message": "Error description",
    "status": "error",
    "data": {}
}
```

## Validation Error Response Format

```json
{
    "message": "Validation failed",
    "status": "error",
    "data": {
        "start_date": ["The start date field is required."],
        "end_date": ["The end date must be after or equal to start date."]
    }
}
```

## Data Structure

### UCR Card Data Fields

| Field        | Type      | Description                                                  |
| ------------ | --------- | ------------------------------------------------------------ |
| id           | integer   | Primary key                                                  |
| net_id       | string    | Network ID                                                   |
| ssn          | string    | Social Security Number                                       |
| student_id   | string    | Student ID                                                   |
| iso          | string    | ISO number                                                   |
| lib_num      | string    | Library number                                               |
| status1      | string    | Status (e.g., "Active Student", "Faculty")                   |
| class        | string    | Class type (U=Undergraduate, G=Graduate, F=Faculty, S=Staff) |
| yr_in_school | string    | Year in school (U1-U4, G1-G3, etc.)                          |
| stud_fac     | string    | Student/Faculty flag (S=Student, F=Faculty)                  |
| prox_int     | string    | Internal proximity number                                    |
| prox_ext     | string    | External proximity number                                    |
| prox_status  | string    | Proximity status (A=Active, I=Inactive)                      |
| issued       | date      | Date card was issued                                         |
| edit_date    | date      | Date record was last edited                                  |
| photo_date   | date      | Date photo was taken                                         |
| imported     | date      | Date record was imported                                     |
| load_status  | string    | Load status                                                  |
| created_at   | timestamp | Record creation timestamp                                    |
| updated_at   | timestamp | Record update timestamp                                      |

## Usage Examples

### Python Example

```python
import requests

# Setup
base_url = "https://your-api-domain.com/api/v1/ucr-card-data"
headers = {
    "Authorization": "Bearer your_token_here",
    "Content-Type": "application/json"
}

# Search by net_id
response = requests.get(f"{base_url}/list?net_id=jdoe001", headers=headers)
data = response.json()

# Search by date range
payload = {
    "start_date": "2024-01-01",
    "end_date": "2024-12-31"
}
response = requests.post(f"{base_url}/date-range", json=payload, headers=headers)
data = response.json()
```

### cURL Examples

```bash
# Get all records
curl -H "Authorization: Bearer your_token" \
     https://your-api-domain.com/api/v1/ucr-card-data/list

# Search by net_id
curl -H "Authorization: Bearer your_token" \
     "https://your-api-domain.com/api/v1/ucr-card-data/list?net_id=jdoe001"

# Search by date range
curl -X POST \
     -H "Authorization: Bearer your_token" \
     -H "Content-Type: application/json" \
     -d '{"start_date":"2024-01-01","end_date":"2024-12-31"}' \
     https://your-api-domain.com/api/v1/ucr-card-data/date-range
```

## Rate Limiting

The API may be subject to rate limiting. Check response headers for rate limit information.

## Troubleshooting

### Common Issues

1. **401 Unauthorized**: Ensure you have a valid Sanctum token and it's included in the Authorization header.

2. **404 Not Found**: The search criteria didn't match any records. Try different search parameters.

3. **422 Validation Error**: Check that date formats are correct (YYYY-MM-DD) and that end_date is not before start_date.

4. **400 Bad Request**: Usually indicates invalid date format in query parameters.

### Support

For technical support or questions about the API, please contact the development team.
