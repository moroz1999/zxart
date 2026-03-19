# structureElement

## Active-state flags: `requested` and `final`

`structureManager` automatically sets two boolean flags on every loaded element to indicate where the current request sits in the site tree.

| Flag | Value | Meaning |
|---|---|---|
| `requested` | `true` | This element is an **ancestor** of the current page — the request is somewhere inside its subtree. Always `true` on the `final` element too. |
| `final` | `true` | This element **is** the current page — the exact endpoint of the requested URL. |

Both flags default to `false` and are set at load time by `structureManager`. No manual work is needed; just read the property.

### Usage example

```php
// In a structureElement or template:
if ($element->final) {
    // render as the current active page
} elseif ($element->requested) {
    // render as an open/ancestor section
}
```

### Angular equivalent

In Angular (client-side), the same semantics are reproduced via URL matching:

```typescript
// requested: we are somewhere inside this item's subtree
isActive(item: MenuItem): boolean {
  const path = window.location.pathname;
  return path.startsWith(item.url) && item.url !== '/';
}
```

There is no need to expose `requested`/`final` through the REST API for this purpose — Angular derives them from `window.location`.
