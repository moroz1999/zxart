import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {BehaviorSubject, combineLatest, Subscription} from 'rxjs';
import {switchMap, tap} from 'rxjs/operators';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxSelectComponent, ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {
  ZxSkeletonBoneComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ActiveAuthorsService} from '../../services/active-authors.service';
import {ActiveAuthor} from '../../models/active-author';

const DEFAULT_ACTIVE_YEARS = 2;
const MIN_ACTIVE_YEARS = 1;
const MAX_ACTIVE_YEARS = 5;

/**
 * Self-contained "active authors" section: authors with works published in the
 * last N years (selectable, 1-5). Reusable for both graphics and music via the
 * `items` input.
 */
@Component({
  selector: 'zx-active-authors, zx-active-authors-view',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxStackComponent,
    ZxPanelComponent,
    ZxInlineComponent,
    ZxSelectComponent,
    HeadingDirective,
    TextDirective,
    ZxSkeletonBoneComponent,
  ],
  templateUrl: './zx-active-authors.component.html',
  styleUrls: ['./zx-active-authors.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxActiveAuthorsComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  /** Content scope: 'graphics' or 'music' (drives the query and the heading) */
  @Input() items: 'graphics' | 'music' = 'graphics';

  authors: ActiveAuthor[] = [];
  loading = true;
  readonly skeletonItems = Array.from({length: 24});
  yearsOptions: ZxSelectOption[] = [];
  selectedYears = String(DEFAULT_ACTIVE_YEARS);

  private readonly years$ = new BehaviorSubject<number>(DEFAULT_ACTIVE_YEARS);
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly activeAuthorsService: ActiveAuthorsService,
    private readonly translateService: TranslateService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.buildYearsOptions();
    this.subscriptions.add(
      this.translateService.onLangChange.subscribe(() => this.buildYearsOptions()),
    );

    this.subscriptions.add(
      this.years$.pipe(
        tap(() => {
          this.loading = true;
          this.cdr.markForCheck();
        }),
        switchMap(years => this.activeAuthorsService.getActive(this.elementId, this.items, years)),
      ).subscribe(authors => {
        this.authors = authors;
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onYearsChange(value: string): void {
    this.selectedYears = value;
    this.years$.next(Number(value));
  }

  private buildYearsOptions(): void {
    const counts: number[] = [];
    for (let years = MIN_ACTIVE_YEARS; years <= MAX_ACTIVE_YEARS; years++) {
      counts.push(years);
    }

    // translate.get() resolves once the async translation file is loaded.
    this.subscriptions.add(
      combineLatest(
        counts.map(count => this.translateService.get('authors-page.active.yearsShort', {count})),
      ).subscribe(labels => {
        this.yearsOptions = counts.map((count, index) => ({value: String(count), label: labels[index]}));
        this.cdr.markForCheck();
      }),
    );
  }
}
