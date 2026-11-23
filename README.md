<p align="center" style="align:top;"><a href="https://github.com/ucrlibrary" target="_blank"><img src="./resources/images/UCR-horizontal-logo-over-white-bg-w-yellow-line.svg" width="250" alt="UCR Library"></a></p>
<hr/>

## About Library Public API
The library public API is the gateway for calling other restFUL API that the library has access to.

## Technology Stack
- Laravel Framework
- PHP 8.2+
- MySQL
- Laravel Forge
- NginX

## Setup and Run for Local Development
- Check docker-compose.yml 

## API Service Scaffolding
- Create a new API service scaffold (controller, service, provider, model, and config registration):
  - `a app:scaffold-api-service LibraryStatus`
- Running in Docker container `api-gateway`:
  - `docker exec api-gateway php artisan app:scaffold-api-service LibraryStatus`
- Optional flags:
  - `--service-key=libraryStatus`
  - `--controller-namespace=v1`
  - `--model-name=LibraryStatusRecord`
  - `--with-route`
  - `--route-uri=library-status`
  - `--force`

## Deployment to AWS via Laravel Forge
1.

## Security Baseline Configuration
- Keep `APP_DEBUG=false` outside local troubleshooting.
- Set `SANCTUM_EXPIRATION` to a short value (default `60` minutes).
- Restrict CORS using:
  - `CORS_ALLOWED_ORIGINS`
  - `CORS_ALLOWED_METHODS`
  - `CORS_ALLOWED_HEADERS`
  - `CORS_MAX_AGE`
  - `CORS_SUPPORTS_CREDENTIALS`
- Always provide a strong `APP_ENCRYPTION_KEY` in deployed environments.

## API Key Lifecycle and Token Abilities
- Application API keys are now stored as one-way hashes and support lifecycle metadata (rotated/revoked/last used).
- New key lifecycle endpoints:
  - `POST /api/v1/applications/{application}/rotate-key`
  - `POST /api/v1/applications/{application}/revoke-key`
- Per-application ability policy:
  - `allowed_abilities` controls app token scopes (`gateway:read`, `gateway:write`)
  - App entry token issuance is restricted to each application's configured abilities
- Gateway routes require Sanctum abilities:
  - `gateway:read` and `gateway:write` for app-issued tokens
  - `user:admin` for user-issued admin tokens