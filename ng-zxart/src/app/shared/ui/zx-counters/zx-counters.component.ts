import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {NgFor} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {TextDirective} from '../typography/directives/text.directive';

/** One "value + word" statistic of a `zx-counters` strip. */
export interface ZxCounterItem {
  readonly value: number | string;
  /** Translation key of the counter word. */
  readonly labelKey: string;
}

/**
 * Hero statistics strip: a row of "**N** word" counters. The overall rating is
 * passed as the first item on pages that have one.
 */
@Component({
  selector: 'zx-counters',
  standalone: true,
  imports: [NgFor, TranslateModule, TextDirective],
  templateUrl: './zx-counters.component.html',
  styleUrl: './zx-counters.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCountersComponent {
  @Input() items: ZxCounterItem[] = [];

  trackByLabel(_index: number, item: ZxCounterItem): string {
    return item.labelKey;
  }
}
