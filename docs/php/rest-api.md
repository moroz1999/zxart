# REST API and DTOs

## DTOs Design
- DTOs must be 100% immutable. Use `readonly class` and constructor property promotion.
- All interfaces and DTOs must be stored in the `models/` folder within the corresponding module/feature. Do not mix type definitions with service or component code.

## REST API Pattern
The workflow for REST API:
1. Service retrieves entities from structure manager and/or data from repositories.
2. Service returns its own internal DTOs to the controller.
3. Controller maps service DTOs to REST DTOs using `Symfony\Component\ObjectMapper\ObjectMapper`.

## Request Bodies: deserialize, do not read arrays

JSON request bodies are turned into **typed request DTOs** by `symfony/serializer`. A controller
must not pass `json_decode(..., true)` output down into services, and must not hand-parse it into
array shapes.

- Inject `Symfony\Component\Serializer\SerializerInterface` and call
  `deserialize($jsonBody, MyRequestDto::class, 'json')`.
- The instance is built by `ZxArt\Shared\Serializer\RequestDenormalizerFactory` (wired in
  `project/core/di-definitions.php`). Tests use the same factory, so they exercise the real
  configuration.
- Request DTOs are `readonly` with promoted constructor properties, like every other DTO.
  Backed enums are denormalized from their scalar value automatically.
- **Nested collections need a docblock.** A native `array` type says nothing about its values, so
  declare `@param array<string, SomeDto> $field` on the constructor — `PhpDocExtractor` reads it and
  builds the nested DTOs. Without it the field stays a raw array.
- Give a property a **default** when it is genuinely optional. A property without one is required,
  and a body missing it fails denormalization.
- Catch `Symfony\Component\Serializer\Exception\ExceptionInterface` and answer **400** — a body that
  does not fit the DTO is a bad request. Its message names the offending field, so pass it through.

Split the responsibility: the serializer rejects what is *malformed* (unknown enum value, wrong
type, missing required field); the **service** rejects what is *invalid* for the domain (blank
label, bad code shape, duplicate, still in use) and can say precisely why.

`ZxArt\Controllers\HardwareData` with `ZxArt\Hardware\Dto\HardwareSaveDto` is the reference.

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

## HTTP Status Codes
- Controllers MUST use proper HTTP status codes. Never return HTTP 200 when an error occurred.
- Use `CmsHttpResponse::getInstance()->setStatusCode('500')` for internal errors.
- Use `CmsHttpResponse::getInstance()->setStatusCode('400')` for bad requests (e.g. unknown `action` parameter).
- On success, return HTTP 200 (default); the JSON response body IS the data directly (array or object).
- On error, return the appropriate HTTP error code; the body is `{"errorMessage": "..."}`.
- Use `$this->renderer->assign('body', $data)` to output the body directly without any envelope wrapper.

## Legacy: `responseStatus` Field
- The `responseStatus: 'success'|'error'` field in JSON responses is a **legacy pattern**. Do NOT use it in new controllers.
- Existing controllers (Comments, Ratings, Radio, etc.) still use it — migrate them to HTTP status codes when refactoring.
- Frontend code checking `response.responseStatus === 'success'` must be updated to rely on HTTP status codes (Angular `HttpClient` throws on non-2xx; handle via `catchError`).

## API Verification and Documentation
- After API changes, verify by sending a request to `http://zxart.loc/` (e.g. `curl http://zxart.loc/firstpage/?action=newPictures&limit=5`). Use this for any API endpoints that don't require authorization.
- After changing the API, you MUST update the existing OpenAPI YAML file or add a new one in `api/api.yaml`.
