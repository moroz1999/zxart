import {CommonModule} from '@angular/common';
import {
  AfterViewInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, Observable} from 'rxjs';
import {filter, shareReplay, switchMap, take} from 'rxjs/operators';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {StatsTopUsersSection} from '../../models/stats.models';
import {StatsService} from '../../services/stats.service';
import {ZxStatsTopTableComponent} from '../zx-stats-top-table/zx-stats-top-table.component';
import {ZxStatsSectionSkeletonComponent} from '../zx-stats-section-skeleton/zx-stats-section-skeleton.component';

@Component({
  selector: 'zx-stats-users',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxGridComponent,
    ZxStatsTopTableComponent,
    ZxStatsSectionSkeletonComponent,
  ],
  templateUrl: './zx-stats-users.component.html',
  styleUrl: './zx-stats-users.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsUsersComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('votersBlock', {static: true}) private votersBlock!: ElementRef<HTMLElement>;
  @ViewChild('commentsBlock', {static: true}) private commentsBlock!: ElementRef<HTMLElement>;
  @ViewChild('tagsBlock', {static: true}) private tagsBlock!: ElementRef<HTMLElement>;

  voters$!: Observable<StatsTopUsersSection | null>;
  comments$!: Observable<StatsTopUsersSection | null>;
  tags$!: Observable<StatsTopUsersSection | null>;

  private readonly votersVisible$ = new BehaviorSubject(false);
  private readonly commentsVisible$ = new BehaviorSubject(false);
  private readonly tagsVisible$ = new BehaviorSubject(false);
  private readonly intersectionObservers: IntersectionObserver[] = [];

  constructor(
    private readonly statsService: StatsService,
    private readonly changeDetector: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.voters$ = this.visibleOnce(this.votersVisible$).pipe(
      switchMap(() => this.statsService.usersTop('voters')),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.comments$ = this.visibleOnce(this.commentsVisible$).pipe(
      switchMap(() => this.statsService.usersTop('comments')),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.tags$ = this.visibleOnce(this.tagsVisible$).pipe(
      switchMap(() => this.statsService.usersTop('tags')),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  ngAfterViewInit(): void {
    this.observeBlock(this.votersBlock, this.votersVisible$);
    this.observeBlock(this.commentsBlock, this.commentsVisible$);
    this.observeBlock(this.tagsBlock, this.tagsVisible$);
  }

  ngOnDestroy(): void {
    this.intersectionObservers.forEach(observer => observer.disconnect());
  }

  private visibleOnce(source$: Observable<boolean>): Observable<boolean> {
    return source$.pipe(
      filter(Boolean),
      take(1),
    );
  }

  private observeBlock(elementRef: ElementRef<HTMLElement>, visible$: BehaviorSubject<boolean>): void {
    const observer = new IntersectionObserver(entries => {
      if (!entries.some(entry => entry.isIntersecting)) {
        return;
      }

      visible$.next(true);
      observer.disconnect();
      this.changeDetector.markForCheck();
    }, {rootMargin: '0px'});
    observer.observe(elementRef.nativeElement);
    this.intersectionObservers.push(observer);
  }
}
