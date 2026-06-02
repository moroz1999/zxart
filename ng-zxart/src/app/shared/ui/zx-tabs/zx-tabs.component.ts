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
import { ZxTabComponent } from './zx-tab.component';

@Component({
  selector: 'zx-tabs',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-tabs.component.html',
  styleUrl: './zx-tabs.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTabsComponent implements AfterContentInit {
  @ContentChildren(ZxTabComponent, { descendants: false }) tabs!: QueryList<ZxTabComponent>;
  @Output() readonly tabChange = new EventEmitter<number>();

  activeIndex = 0;
  private pendingActiveIndex: number | null = null;

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

  activateTab(index: number): void {
    this.activeIndex = index;
    this.cdr.markForCheck();
  }

  activateLinkedTab(index: number, event: MouseEvent): void {
    if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
      return;
    }

    event.preventDefault();

    const href = this.tabs.get(index)?.href;
    if (href) {
      window.history.pushState(null, '', href);
    }

    this.activateTab(index);
    this.tabChange.emit(index);
  }

  get activeTemplateRef(): TemplateRef<unknown> | null {
    const tab = this.tabs?.get(this.activeIndex);
    return tab?.contentDirective?.templateRef ?? null;
  }
}
