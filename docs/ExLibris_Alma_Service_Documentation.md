# ExLibris Alma Services Documentation

This documentation covers the ExLibris Alma integration services in the [`app/Services/ExLibrisAlma`](app/Services/ExLibrisAlma ) folder and related files.

## Overview

The ExLibris Alma services provide integration with the ExLibris Alma library management system via an API, offering patron data management, fee tracking, and guest authentication capabilities.

## Core Services

### ExLibrisAlmaPatronDataService

Located in [`app/Services/ExLibrisAlma/ExLibrisAlmaPatronDataService.php`](app/Services/ExLibrisAlma/ExLibrisAlmaPatronDataService.php )

This service handles patron-related operations with the ExLibris Alma API.

**Key Methods:**
- [`Patron`](app/Services/ExLibrisAlma/ExLibrisAlmaPatronDataService.php ): Retrieves patron data by string ID
- [`GuestLogin`](app/Services/ExLibrisAlma/ExLibrisAlmaPatronDataService.php ): Handles guest authentication using encrypted credentials
- [`Search`](app/Services/ExLibrisAlma/ExLibrisAlmaPatronDataService.php ): Searches for patrons based on query parameters

**Constructor Parameters:**
- `$key`: API key for ExLibris Alma
- `$baseUrl`: Base URL for the API
- `$singleDataMinutes`: Cache duration for single data requests
- `$multiDataMinutes`: Cache duration for multiple data requests

### ExLibrisAlmaFeeDataService

Located in [`app/Services/ExLibrisAlma/ExLibrisAlmaFeeDataService.php`](app/Services/ExLibrisAlma/ExLibrisAlmaFeeDataService.php )

This service manages fee-related operations for library patrons.

**Key Methods:**
- [`Fees`](app/Services/ExLibrisAlma/ExLibrisAlmaFeeDataService.php ): Retrieves fees for a specific patron by string ID
- [`getResponse`](app/Services/ExLibrisAlma/ExLibrisAlmaFeeDataService.php ): Internal method for handling API responses

## Data Transfer Objects (DTOs)

### PatronData

Located in [`app/Services/ExLibrisAlma/DataTransferObjects/PatronData.php`](app/Services/ExLibrisAlma/DataTransferObjects/PatronData.php )

Represents patron information with the following properties:
- `$primaryId`: Primary identifier
- `$firstName`, `$middleName`, `$lastName`: Name components
- `$recordType`: Type of patron record
- `$userGroup`, `$userGroupDesc`: User group information
- `$accountType`: Account type
- `$status`: Patron status
- `$expiryDate`: Account expiry date
- Address fields: `$addressLine1`, `$addressLine2`, `$addressCity`, `$addressStateProvince`, `$addressPostalCode`, `$addressCountry`
- Contact: `$email`, `$phone`
- Identifiers: `$identifierNetId`, `$identifierBarcode`, `$identifierNetIdEmail`

**Key Methods:**
- [`fromArray`](app/Services/ExLibrisAlma/DataTransferObjects/PatronData.php ): Creates instance from array data
- [`fromCollection`](app/Services/ExLibrisAlma/DataTransferObjects/PatronData.php ): Creates collection of PatronData from array
- [`getPreferredContactInfoIndex`](app/Services/ExLibrisAlma/DataTransferObjects/PatronData.php ): Helper to find preferred contact info
- [`getIdentifierIndex`](app/Services/ExLibrisAlma/DataTransferObjects/PatronData.php ): Helper to find specific identifier types

### FeeData

Located in [`app/Services/ExLibrisAlma/DataTransferObjects/FeeData.php`](app/Services/ExLibrisAlma/DataTransferObjects/FeeData.php )

Represents fee information with properties:
- `$link`: Link to fee details
- `$id`: Fee ID
- `$type`, `$typeDesc`: Fee type and description
- `$status`: Fee status
- `$userPrimaryId`: Associated patron ID
- `$balance`: Current balance
- `$remainingVatAmount`: Remaining VAT amount
- `$originalAmount`, `$originalVatAmount`: Original amounts
- `$creationTime`, `$statusTime`: Timestamps
- `$owner`, `$ownerDesc`: Owner information
- `$title`: Fee title
- `$barcode`: Associated barcode

**Key Methods:**
- [`fromArray`](app/Services/ExLibrisAlma/DataTransferObjects/FeeData.php ): Creates instance from array data

## Service Provider

### ExLibrisAlmaDataServiceProvider

Located in [`app/Providers/ExLibrisAlmaDataServiceProvider.php`](app/Providers/ExLibrisAlmaDataServiceProvider.php )

Registers the ExLibris Alma services as singletons in the Laravel service container.

**Registered Services:**
- [`ExLibrisAlmaFeeDataService`](app/Services/ExLibrisAlma/ExLibrisAlmaFeeDataService.php )
- [`ExLibrisAlmaPatronDataService`](app/Services/ExLibrisAlma/ExLibrisAlmaPatronDataService.php )

## Controller Integration

### ExLibrisAlmaController

Located in [`app/Http/Controllers/Api/v1/ExLibrisAlmaController.php`](app/Http/Controllers/Api/v1/ExLibrisAlmaController.php )

Provides HTTP endpoints for ExLibris Alma operations:
- [`fees`](app/Http/Controllers/Api/v1/ExLibrisAlmaController.php ): GET endpoint for patron fees
- [`patron`](app/Http/Controllers/Api/v1/ExLibrisAlmaController.php ): GET endpoint for patron data
- [`guestLogin`](app/Http/Controllers/Api/v1/ExLibrisAlmaController.php ): POST endpoint for guest authentication
- [`search`](app/Http/Controllers/Api/v1/ExLibrisAlmaController.php ): GET endpoint for patron search

## Configuration

### Services Configuration

Located in [`config/services.php`](config/services.php )

The ExLibris configuration includes:
```php
'exLibris' => [
    'key' => env("EXLIBRIS_RW_API_KEY"),
    'baseUrl' => env("EXLIBRIS_API_BASEURL"),
    'singleDataMinutes' => env("SINGLE_DATA_MINUTES"),
    'multiDataMinutes' => env("MULTI_DATA_MINUTES"),
],
```

### Service Provider Registration

The service provider is registered in [`config/app.php`](config/app.php ):
```php
App\Providers\ExLibrisAlmaDataServiceProvider::class,
```

## API Routes

Located in [`routes/api_v1.php`](routes/api_v1.php )

Protected routes under `auth:sanctum` middleware:
- `POST /alma-user/patron/guestLogin/{stringCreds}`
- `GET /alma-user/patron/{stringId}`
- `GET /alma-user/patron-search`
- `GET /alma-user/fees/{stringId}`

## Key Features

1. **Authentication**: Guest login using encrypted credentials
2. **Patron Management**: Retrieve and search patron data
3. **Fee Tracking**: Access patron fee information
4. **Data Transformation**: Convert API responses to structured DTOs
5. **Caching**: Built-in caching for performance optimization
6. **Error Handling**: Comprehensive error handling with proper HTTP status codes

## Dependencies

- Uses [`ApiResponses`](app/Traits/ApiResponses.php ) trait for consistent API responses
- Uses [`EncryptionHelper`](app/Helpers/EncryptionHelper.php ) for credential decryption
- Integrates with Laravel's HTTP client and caching systems
- Uses Illuminate collections for data manipulation