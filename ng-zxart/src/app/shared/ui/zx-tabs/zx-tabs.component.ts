import {
  AfterContentInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ContentChildren,
  QueryList,
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

  constructor(private readonly cdr: ChangeDetectorRef) {}

  ngAfterContentInit(): void {
    this.activateTab(0);
  }

  activateTab(index: number): void {
    this.activeIndex = index;
    this.tabs.forEach((tab, i) => tab.setActive(i === index));
    this.cdr.markForCheck();
  }
}
