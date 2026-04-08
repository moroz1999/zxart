# SVG Icons

The project uses `angular-svg-icon` (`SvgIconComponent`) to render inline SVG icons.

## Sizing rule

Always set `width`/`height` on the `svg-icon` host element via a CSS class. Never target the inner `<svg>`.

```html
<svg-icon name="search" class="my-icon"></svg-icon>
```
```scss
.my-icon {
  width: 20px;
  height: 20px;
}
```

`svg-icon` is a custom element — `display: inline` by default, which ignores CSS `width`/`height`. A global rule in `styles.scss` forces `display: inline-flex` and makes the inner `<svg>` fill 100% of the host. Size must be set on the host.

## Forbidden patterns

```scss
/* WRONG — targets inner svg */
svg-icon svg { width: 20px; height: 20px; }

/* WRONG — uses ::ng-deep */
:host ::ng-deep svg-icon svg { width: 20px; }
```

## Additional rules

- Icon sizes use `px` or CSS variables — no `em`, no `rem`.
- For animated transforms (e.g., rotate on expand), apply `transition`/`transform` to `svg-icon`, not the inner `<svg>`.
