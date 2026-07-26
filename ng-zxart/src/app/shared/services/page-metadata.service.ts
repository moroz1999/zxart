import {DOCUMENT} from '@angular/common';
import {Inject, Injectable} from '@angular/core';
import {Meta, Title} from '@angular/platform-browser';
import {ActivatedRouteSnapshot, NavigationEnd, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {EMPTY, merge, Observable, of, Subject} from 'rxjs';
import {distinctUntilChanged, filter, ignoreElements, map, shareReplay, startWith, switchMap, tap} from 'rxjs/operators';
import {PageMetadataDto} from '../models/page-metadata.dto';

const EMPTY_METADATA: PageMetadataDto = {
  title: '',
  description: '',
  noIndex: false,
  openGraph: {},
  twitter: {},
  languageLinks: {},
  structuredData: null,
};

interface TranslatedTitleRequest {
  url: string;
  titleKey: string;
  params: Record<string, string>;
}

@Injectable({providedIn: 'root'})
export class PageMetadataService {
  private readonly entityMetadata = new Subject<PageMetadataDto>();
  private readonly translatedTitle = new Subject<TranslatedTitleRequest>();

  private readonly routeMetadata$: Observable<PageMetadataDto> = this.router.events.pipe(
    filter((event): event is NavigationEnd => event instanceof NavigationEnd),
    map(() => this.deepestRoute(this.router.routerState.snapshot.root)),
    distinctUntilChanged(),
    switchMap(route => route.data['metadataSource'] === 'entity'
      ? EMPTY
      : this.loadLocalMetadata(route)),
  );

  private readonly translatedTitleEffect$: Observable<never> = this.translatedTitle.pipe(
    switchMap(request => this.translate.stream(request.titleKey, request.params).pipe(
      filter(() => this.router.url === request.url),
      tap(title => this.title.setTitle(`${String(title)} - ZX-Art`)),
    )),
    ignoreElements(),
  );

  readonly metadata$: Observable<PageMetadataDto> = merge(
    this.routeMetadata$,
    this.entityMetadata,
    this.translatedTitleEffect$,
  ).pipe(
    tap(metadata => this.apply(metadata)),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private readonly router: Router,
    private readonly title: Title,
    private readonly meta: Meta,
    private readonly translate: TranslateService,
    @Inject(DOCUMENT) private readonly document: Document,
  ) {}

  applyEntityMetadata(metadata: PageMetadataDto): void {
    this.entityMetadata.next(metadata);
  }

  applyTranslatedTitle(titleKey: string, params: Record<string, string>): void {
    this.translatedTitle.next({url: this.router.url, titleKey, params});
  }

  applyFormTitle(route: ActivatedRouteSnapshot, entityTitle: string): void {
    const titleKey = route.data['formTitleKey'] as string | undefined;
    if (titleKey) {
      this.applyTranslatedTitle(titleKey, {title: entityTitle});
    }
  }

  private loadLocalMetadata(route: ActivatedRouteSnapshot): Observable<PageMetadataDto> {
    const titleKey = route.data['titleKey'] as string | undefined;
    if (!titleKey) {
      return of({...EMPTY_METADATA, title: 'ZX-Art', noIndex: route.data['noIndex'] === true});
    }

    return this.translate.onLangChange.pipe(
      startWith(null),
      map(() => this.translate.instant(titleKey)),
      map(title => ({...EMPTY_METADATA, title: `${title} - ZX-Art`, noIndex: route.data['noIndex'] === true})),
    );
  }

  private deepestRoute(route: ActivatedRouteSnapshot): ActivatedRouteSnapshot {
    return route.firstChild ? this.deepestRoute(route.firstChild) : route;
  }

  private apply(metadata: PageMetadataDto): void {
    this.title.setTitle(metadata.title);
    this.setNamedTag('description', metadata.description);
    this.setNamedTag('robots', metadata.noIndex ? 'noindex' : '');
    this.replacePropertyTags('og:', metadata.openGraph);
    this.replacePropertyTags('twitter:', metadata.twitter);
    this.replaceLanguageLinks(metadata.languageLinks);
    this.replaceStructuredData(metadata.structuredData);
  }

  private setNamedTag(name: string, content: string): void {
    if (content === '') {
      this.meta.removeTag(`name="${name}"`);
      return;
    }
    this.meta.updateTag({name, content});
  }

  private replacePropertyTags(prefix: string, values: Record<string, string>): void {
    this.document.head.querySelectorAll(`meta[property^="${prefix}"]`).forEach(tag => tag.remove());
    Object.entries(values).forEach(([key, content]) => {
      const tag = this.document.createElement('meta');
      tag.setAttribute('property', `${prefix}${key}`);
      tag.setAttribute('content', content);
      this.document.head.appendChild(tag);
    });
  }

  private replaceLanguageLinks(values: Record<string, string>): void {
    this.document.head.querySelectorAll('link[rel="alternate"][hreflang]').forEach(link => link.remove());
    Object.entries(values).forEach(([language, href]) => {
      const link = this.document.createElement('link');
      link.rel = 'alternate';
      link.hreflang = language;
      link.href = href;
      this.document.head.appendChild(link);
    });
  }

  private replaceStructuredData(value: unknown): void {
    this.document.head.querySelector('script[data-page-metadata="structured-data"]')?.remove();
    if (value === null) {
      return;
    }
    const script = this.document.createElement('script');
    script.type = 'application/ld+json';
    script.dataset['pageMetadata'] = 'structured-data';
    script.text = JSON.stringify(value);
    this.document.head.appendChild(script);
  }
}
