# zxProdsGrid

Fixed-width product card grid layout directive.

`ng-zxart/src/app/shared/directives/prods-grid.directive.ts`

## Contract

- Apply `zxProdsGrid` to a container of `zx-prod-block` cards or card wrappers.
- Each desktop grid column is exactly `256px`; product cards must not stretch to consume remaining row width.
- When the viewport is narrower than a card, its single column shrinks only enough to avoid overflow.
- Use this directive instead of feature-specific product grid SCSS.
