import {
  AfterContentInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ContentChildren,
  EventEmitter,
  Input,
  Output,
  QueryList,
  TemplateRef,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { ZxTabComponent } from './zx-tab.component';

@Component({
  selector: 'zx-tabs',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './zx-tabs.component.html',
  styleUrl: './zx-tabs.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTabsComponent implements AfterContentInit {
  @ContentChildren(ZxTabComponent, { descendants: false }) tabs!: QueryList<ZxTabComponent>;
  @Output() readonly tabChange = new EventEmitter<number>();

  activeIndex = 0;
  private pendingActiveIndex: number | null = null;
  private activated = false;

  @Input() set initialActiveIndex(val: number) {
    if (this.tabs) {
      this.activateTab(val);
    } else {
      this.pendingActiveIndex = val;
    }
  }

  constructor(private readonly cdr: ChangeDetectorRef) {}

  ngAfterContentInit(): void {
    this.activateTab(this.pendingActiveIndex ?? 0);
    this.pendingActiveIndex = null;
  }

  /**
   * Routed tabs reach this through the route (RouterLink navigates, the parent
   * feeds the new index back in), local tabs straight from the click. The change
   * event fires only on a real switch, never on the initial activation.
   */
  activateTab(index: number): void {
    const changed = this.activated && index !== this.activeIndex;
    this.activeIndex = index;
    this.activated = true;
    this.cdr.markForCheck();
    if (changed) {
      this.tabChange.emit(index);
    }
  }

  get activeTemplateRef(): TemplateRef<unknown> | null {
    const tab = this.tabs?.get(this.activeIndex);
    return tab?.contentDirective?.templateRef ?? null;
  }
}
