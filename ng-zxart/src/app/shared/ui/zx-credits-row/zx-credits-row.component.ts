import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {NgFor, NgIf} from '@angular/common';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {TextDirective} from '../typography/directives/text.directive';

/** A person or group credited in a `zx-credits-row` group. */
export interface ZxCreditPersonDto {
  readonly title: string;
  readonly url: string;
}

/** One "Role: names" group of a credits row. */
export interface ZxCreditGroup {
  /** Translation key of the role label; the colon is added by the component. */
  readonly labelKey: string;
  readonly people: ZxCreditPersonDto[];
  /** Optional trailing note rendered after the names, e.g. a compo placement. */
  readonly note?: string | null;
}

/**
 * Hero credits row: "Role: names · Role: names". The single rendering of people
 * credits across the work pages.
 */
@Component({
  selector: 'zx-credits-row',
  standalone: true,
  imports: [NgFor, NgIf, RouterLink, TranslateModule, TextDirective],
  templateUrl: './zx-credits-row.component.html',
  styleUrl: './zx-credits-row.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCreditsRowComponent {
  @Input() groups: ZxCreditGroup[] = [];

  get visibleGroups(): ZxCreditGroup[] {
    return this.groups.filter(group => group.people.length > 0);
  }

  trackByLabel(_index: number, group: ZxCreditGroup): string {
    return group.labelKey;
  }

  trackByUrl(_index: number, person: ZxCreditPersonDto): string {
    return person.url;
  }
}
