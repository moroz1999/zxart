import {
  AfterContentInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ContentChildren,
  Input,
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

  get activeTemplateRef(): TemplateRef<unknown> | null {
    const tab = this.tabs?.get(this.activeIndex);
    return tab?.contentDirective?.templateRef ?? null;
  }
}
