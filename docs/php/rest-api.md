# REST API and DTOs

## DTOs Design
- DTOs must be 100% immutable. Use `readonly class` and constructor property promotion.
- All interfaces and DTOs must be stored in the `models/` folder within the corresponding module/feature. Do not mix type definitions with service or component code.

## REST API Pattern
The workflow for REST API:
1. Service retrieves entities from structure manager and/or data from repositories.
2. Service returns its own internal DTOs to the controller.
3. Controller maps service DTOs to REST DTOs using `Symfony\Component\ObjectMapper\ObjectMapper`.

## ObjectMapper Usage
- Mapping objects from service DTOs to REST DTOs (and vice-versa if needed) must be done exclusively through `ObjectMapper`.
- Example of `ObjectMapper` usage in controller:
```php
$internalDto = $this->myService->getData();
$restDto = $this->objectMapper->map($internalDto, MyRestDto::class);
// For arrays:
$restDtos = array_map(fn($dto) => $this->objectMapper->map($dto, MyRestDto::class), $internalDtos);
```
- Do NOT use `dataResponseConverters` for new code. All new REST endpoints must follow the DTO mapping scheme described above.

## API Verification and Documentation
- After API changes, verify by sending a request to `http://zxart.loc/` (e.g. `curl http://zxart.loc/firstpage/?action=newPictures&limit=5`). Use this for any API endpoints that don't require authorization.
- After changing the API, you MUST update the existing OpenAPI YAML file or add a new one in `api/api.yaml`.
