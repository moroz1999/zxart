import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {RouterLink} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, Subject, Subscription} from 'rxjs';
import {switchMap, tap} from 'rxjs/operators';
import {ActiveAuthor} from '../../models/active-author';
import {ActiveAuthorsService} from '../../services/active-authors.service';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxSelectComponent, ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxActiveAuthorsSkeletonComponent
} from '../zx-active-authors-skeleton/zx-active-authors-skeleton.component';

const DEFAULT_ACTIVE_YEARS = 2;
const MIN_ACTIVE_YEARS = 1;
const MAX_ACTIVE_YEARS = 5;

@Component({
  selector: 'zx-active-authors',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterLink,
    TranslateModule,
    ZxInlineComponent,
    ZxPanelComponent,
    ZxSelectComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    InViewportDirective,
    ZxActiveAuthorsSkeletonComponent,
  ],
  templateUrl: './zx-active-authors.component.html',
  styleUrls: ['./zx-active-authors.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxActiveAuthorsComponent implements OnInit, OnDestroy {
  @Input({required: true}) items: 'graphics' | 'music' = 'graphics';

  authors: ActiveAuthor[] = [];
  loading = true;
  yearsOptions: ZxSelectOption[] = [];
  selectedYears = String(DEFAULT_ACTIVE_YEARS);
  requested = false;

  private readonly years$ = new Subject<number>();
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly activeAuthorsService: ActiveAuthorsService,
    private readonly translateService: TranslateService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.buildYearsOptions();
    this.subscriptions.add(this.translateService.onLangChange.subscribe(() => this.buildYearsOptions()));
    this.subscriptions.add(
      this.years$.pipe(
        tap(() => {
          this.loading = true;
          this.cdr.markForCheck();
        }),
        switchMap(years => this.activeAuthorsService.getActive(this.items, years)),
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
    if (this.requested) {
      this.years$.next(Number(value));
    }
  }

  onInViewport(): void {
    if (this.requested) {
      return;
    }
    this.requested = true;
    this.years$.next(Number(this.selectedYears));
  }

  private buildYearsOptions(): void {
    const counts = Array.from(
      {length: MAX_ACTIVE_YEARS - MIN_ACTIVE_YEARS + 1},
      (_, index) => index + MIN_ACTIVE_YEARS,
    );
    this.subscriptions.add(
      combineLatest(counts.map(count => this.translateService.get('authors-page.active.yearsShort', {count})))
        .subscribe(labels => {
          this.yearsOptions = counts.map((count, index) => ({value: String(count), label: labels[index]}));
          this.cdr.markForCheck();
        }),
    );
  }
}
