import {ChangeDetectionStrategy, Component} from '@angular/core';

/**
 * Two-column page body: a narrow companion column projected into the
 * `[zxSidebar]` slot, the main content in the default slot. The sidebar sticks
 * while the content scrolls; below the tablet breakpoint both columns stack in
 * DOM order, sidebar first.
 */
@Component({
  selector: 'zx-sidebar-layout',
  standalone: true,
  templateUrl: './zx-sidebar-layout.component.html',
  styleUrl: './zx-sidebar-layout.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxSidebarLayoutComponent {}
