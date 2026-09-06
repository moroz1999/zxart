# zx-tree

Collapsible tree of labelled rows with caller-supplied per-row actions.

`ng-zxart/src/app/shared/ui/zx-tree/`

The component owns the nesting and the expanded state and nothing else: what a
row shows beside its label, and what it can do, belongs to whoever renders the
tree. Branches start collapsed — a management tree is deep enough that opening
all of it at once is never the first thing a reader wants.

## Props

| Prop | Type | Description |
|---|---|---|
| `nodes` | `ZxTreeNode[]` | Roots of the tree |
| `actions` | `TemplateRef \| null` | Rendered at the end of every row |
| `expandLabel` | string | `aria-label` of a collapsed row's toggle |
| `collapseLabel` | string | `aria-label` of an expanded row's toggle |

```ts
export interface ZxTreeNode {
  id: number;
  label: string;
  children: ZxTreeNode[];
}
```

## Row actions

The action template is given the node and its depth, so a level can offer its
own actions — a country takes a new city, a city does not. A host that needs
more on a row extends `ZxTreeNode` and reads its own fields in that template;
the tree never looks at them.

```html
<zx-tree [nodes]="nodes" [actions]="rowActions"></zx-tree>

<ng-template #rowActions let-node let-level="level">
  <span appText="caption" tone="muted">{{ 'x.count' | translate: {count: node.usages} }}</span>
  <zx-button *ngIf="level === 0" size="sm" color="transparent">Add</zx-button>
</ng-template>
```
