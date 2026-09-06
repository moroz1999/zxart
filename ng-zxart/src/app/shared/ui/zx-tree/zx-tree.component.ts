import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input, OnInit, TemplateRef} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {TextDirective} from '../typography/directives/text.directive';

/**
 * One row of a `zx-tree`. A node with no children renders no toggle.
 *
 * A host that needs more on a row — a count, a state — extends this interface
 * and reads the extra fields in its own action template; the tree itself only
 * ever needs the label and the nesting.
 */
export interface ZxTreeNode {
  id: number;
  label: string;
  children: ZxTreeNode[];
}

/**
 * Collapsible tree of labelled rows, with a caller-supplied action template per
 * row. It owns nothing but the nesting and the expanded state: what a row shows
 * beside its label, and what it can do, belongs to whoever renders the tree.
 *
 * The action template is given the node and its depth
 * (`let-node`, `let-level="level"`), so a level can offer its own actions — a
 * country takes a new city, a city does not.
 *
 * Branches start collapsed: a management tree is hundreds of rows deep enough
 * that opening all of them at once is never what a reader wants first.
 */
@Component({
  selector: 'zx-tree',
  standalone: true,
  imports: [CommonModule, SvgIconComponent, TextDirective],
  templateUrl: './zx-tree.component.html',
  styleUrl: './zx-tree.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTreeComponent implements OnInit {
  @Input() nodes: ZxTreeNode[] = [];
  /** Rendered at the end of every row, with the node and its level as context. */
  @Input() actions: TemplateRef<unknown> | null = null;
  @Input() expandLabel = '';
  @Input() collapseLabel = '';

  private readonly expanded = new Set<number>();

  constructor(private readonly iconRegistry: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconRegistry.loadSvg(`${environment.svgUrl}expand-more.svg`, 'expand-more')?.subscribe();
    this.iconRegistry.loadSvg(`${environment.svgUrl}expand-less.svg`, 'expand-less')?.subscribe();
  }

  isExpanded(node: ZxTreeNode): boolean {
    return this.expanded.has(node.id);
  }

  toggle(node: ZxTreeNode): void {
    if (this.expanded.has(node.id)) {
      this.expanded.delete(node.id);
      return;
    }
    this.expanded.add(node.id);
  }

  trackById(index: number, node: ZxTreeNode): number {
    return node.id;
  }
}
