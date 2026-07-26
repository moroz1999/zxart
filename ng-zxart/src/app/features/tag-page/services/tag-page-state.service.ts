import {Injectable} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {combineLatest, Observable} from 'rxjs';
import {map, shareReplay, startWith, switchMap, tap} from 'rxjs/operators';
import {PageMetadataDto} from '../../../shared/models/page-metadata.dto';
import {BreadcrumbService} from '../../../shared/services/breadcrumb.service';
import {PageMetadataService} from '../../../shared/services/page-metadata.service';
import {TagPageDto, TagPageSection} from '../models/tag-page.dto';
import {TagPageApiService} from './tag-page-api.service';

export const TAG_PAGE_SORTING_KEYS = [
  'title,asc',
  'title,desc',
  'votes,desc',
  'votes,asc',
  'date,desc',
  'date,asc',
] as const;

export interface TagPageVm {
  readonly loading: boolean;
  readonly tag: TagPageDto | null;
  readonly titleKey: string;
}

interface TagPageRequest {
  readonly tagId: number;
  readonly section: TagPageSection;
  readonly basePath: string;
  readonly titleKey: string;
}

@Injectable()
export class TagPageStateService {
  readonly vm$: Observable<TagPageVm> = combineLatest([
    this.route.paramMap,
    this.route.data,
    this.translate.onLangChange.pipe(startWith(null)),
  ]).pipe(
    map(([params, data]): TagPageRequest => ({
      tagId: Number(params.get('id') ?? 0),
      section: data['section'] as TagPageSection,
      basePath: data['basePath'] as string,
      titleKey: data['titleKey'] as string,
    })),
    switchMap(request => this.tagPageApi.get(request.tagId, request.section).pipe(
      tap(tag => this.applyPageContext(request, tag)),
      map(tag => ({loading: false, tag, titleKey: request.titleKey})),
      startWith({loading: true, tag: null, titleKey: request.titleKey}),
    )),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private readonly route: ActivatedRoute,
    private readonly translate: TranslateService,
    private readonly tagPageApi: TagPageApiService,
    private readonly pageMetadata: PageMetadataService,
    private readonly breadcrumbService: BreadcrumbService,
  ) {}

  private applyPageContext(request: TagPageRequest, tag: TagPageDto | null): void {
    if (tag === null) {
      const title = String(this.translate.instant('common.not-found'));
      const metadata: PageMetadataDto = {
        title: `${title} - ZX-Art`,
        description: '',
        noIndex: true,
        openGraph: {},
        twitter: {},
        languageLinks: {},
        structuredData: null,
      };
      this.pageMetadata.applyEntityMetadata(metadata);
      this.breadcrumbService.setEntityTrail({items: [], currentTitle: title});
      return;
    }

    this.pageMetadata.applyEntityMetadata(tag.metadata);
    this.breadcrumbService.setEntityTrail({
      items: [{
        title: String(this.translate.instant(request.titleKey)),
        url: request.basePath,
      }],
      currentTitle: tag.title,
    });
  }
}
