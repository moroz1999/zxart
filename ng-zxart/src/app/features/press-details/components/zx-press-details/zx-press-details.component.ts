import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output, SimpleChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {map, shareReplay, startWith, tap} from 'rxjs/operators';
import {PressDetailsDto, PressMentionDto} from '../../models/press-details.dto';
import {PressDetailsApiService} from '../../services/press-details-api.service';
import {ZxPressIssueNavComponent} from '../zx-press-issue-nav/zx-press-issue-nav.component';
import {ZxPressDetailsSkeletonComponent} from '../zx-press-details-skeleton/zx-press-details-skeleton.component';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {BreadcrumbService} from '../../../../shared/services/breadcrumb.service';
import {PageMetadataService} from '../../../../shared/services/page-metadata.service';
import {ZxCalloutComponent} from '../../../../shared/ui/zx-callout/zx-callout.component';
import {ZxChipsComponent} from '../../../../shared/ui/zx-chips/zx-chips.component';
import {ZxExtLinkDto, ZxExtLinksComponent} from '../../../../shared/ui/zx-ext-links/zx-ext-links.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxMetaRowComponent} from '../../../../shared/ui/zx-meta-row/zx-meta-row.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxPreformattedComponent} from '../../../../shared/ui/zx-preformatted/zx-preformatted.component';
import {ZxSidebarLayoutComponent} from '../../../../shared/ui/zx-sidebar-layout/zx-sidebar-layout.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

/** Catalogue category the press issues live in; mirrors the main menu entry. */
const PRESS_CATEGORY_ID = 244858;

/** Host name of an absolute URL, for labelling an outbound link. */
const URL_HOST = /^[a-z]+:\/\/([^/?#]+)/i;

/** One labelled row of entities a press article links to. */
interface MentionGroup {
  labelKey: string;
  items: PressMentionDto[];
}

/** Everything the read view renders, derived once per loaded article. */
interface PressDetailsVm {
  details: PressDetailsDto;
  mentionGroups: MentionGroup[];
  previous: PressMentionDto | null;
  next: PressMentionDto | null;
  externalLinks: ZxExtLinkDto[];
}

/** Load state of the read view: exactly one of the three is rendered. */
interface PressDetailsState {
  loading: boolean;
  error: boolean;
  article: PressDetailsVm | null;
}

const LOADING_STATE: PressDetailsState = {loading: true, error: false, article: null};

/** Read view for `press/:id`. */
@Component({
  selector: 'zx-press-details-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    CommentsListComponent,
    ZxCalloutComponent,
    ZxChipsComponent,
    ZxExtLinksComponent,
    ZxGridComponent,
    ZxMetaRowComponent,
    ZxPanelComponent,
    ZxPreformattedComponent,
    ZxPressDetailsSkeletonComponent,
    ZxPressIssueNavComponent,
    ZxSidebarLayoutComponent,
    ZxStackComponent,
    TextDirective,
  ],
  templateUrl: './zx-press-details.component.html',
  styleUrls: ['./zx-press-details.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPressDetailsComponent implements OnChanges {
  @Input() elementId = 0;
  @Output() pageTitleChange = new EventEmitter<string>();

  state$: Observable<PressDetailsState> = of(LOADING_STATE);

  constructor(
    private readonly api: PressDetailsApiService,
    private readonly breadcrumbService: BreadcrumbService,
    private readonly pageMetadata: PageMetadataService,
    private readonly translate: TranslateService,
  ) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (!changes['elementId']) {
      return;
    }
    if (!this.elementId || +this.elementId <= 0) {
      this.state$ = of(LOADING_STATE);
      return;
    }
    this.state$ = this.api.getDetails(+this.elementId).pipe(
      tap(details => {
        if (details) {
          this.applyPageState(details);
        } else {
          this.breadcrumbService.setNotFoundTrail();
        }
      }),
      map(details => ({
        loading: false,
        error: details === null,
        article: details ? this.buildVm(details) : null,
      })),
      startWith(LOADING_STATE),
      // refCount so leaving the page ends the language subscription: otherwise a
      // later language switch would refetch this article and overwrite the title
      // and breadcrumb trail of whatever page the reader moved on to.
      shareReplay({bufferSize: 1, refCount: true}),
    );
  }

  trackByGroup(_index: number, group: MentionGroup): string {
    return group.labelKey;
  }

  private applyPageState(details: PressDetailsDto): void {
    this.pageTitleChange.emit(details.title);
    this.pageMetadata.applyPlainTitle(details.title);
    this.breadcrumbService.setEntityTrail({
      items: [
        {title: this.translate.instant('menu.software'), url: '/prods'},
        {title: this.translate.instant('menu.soft.press'), url: '/prods', queryParams: {cat: PRESS_CATEGORY_ID}},
        ...(details.publication ? [{title: details.publication.title, url: details.publication.url}] : []),
      ],
      currentTitle: details.shortTitle,
    });
  }

  private buildVm(details: PressDetailsDto): PressDetailsVm {
    const articles = details.publication?.articles ?? [];
    const position = articles.findIndex(article => article.id === details.id);

    return {
      details,
      mentionGroups: this.buildMentionGroups(details),
      previous: position > 0 ? articles[position - 1] : null,
      next: position >= 0 && position < articles.length - 1 ? articles[position + 1] : null,
      externalLinks: details.externalLink
        ? [{url: details.externalLink, label: this.linkLabel(details.externalLink)}]
        : [],
    };
  }

  private buildMentionGroups(details: PressDetailsDto): MentionGroup[] {
    return [
      {labelKey: 'press-details.authors', items: details.authors},
      {labelKey: 'press-details.people', items: details.people},
      {labelKey: 'press-details.groups', items: details.groups},
      {labelKey: 'press-details.software', items: details.software},
      {labelKey: 'press-details.pictures', items: details.pictures},
      {labelKey: 'press-details.tunes', items: details.tunes},
      {labelKey: 'press-details.parties', items: details.parties},
    ].filter(group => group.items.length > 0);
  }

  /** The source host reads better than the raw URL next to the "source" label. */
  private linkLabel(url: string): string {
    return URL_HOST.exec(url)?.[1] ?? url;
  }
}
