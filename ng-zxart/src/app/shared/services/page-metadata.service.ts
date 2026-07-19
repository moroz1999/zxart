import {DOCUMENT} from '@angular/common';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import {Meta, Title} from '@angular/platform-browser';
import {NavigationEnd, Router} from '@angular/router';
import {Observable, of} from 'rxjs';
import {catchError, distinctUntilChanged, filter, map, shareReplay, switchMap, tap} from 'rxjs/operators';
import {environment} from '../../../environments/environment';
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

@Injectable({providedIn: 'root'})
export class PageMetadataService {
  readonly metadata$: Observable<PageMetadataDto> = this.router.events.pipe(
    filter((event): event is NavigationEnd => event instanceof NavigationEnd),
    map(event => event.urlAfterRedirects),
    distinctUntilChanged(),
    switchMap(path => this.http.get<PageMetadataDto>(`${environment.apiBaseUrl}page-metadata/`, {
      params: new HttpParams().set('path', path),
    }).pipe(catchError(() => of(EMPTY_METADATA)))),
    tap(metadata => this.apply(metadata)),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private readonly router: Router,
    private readonly http: HttpClient,
    private readonly title: Title,
    private readonly meta: Meta,
    @Inject(DOCUMENT) private readonly document: Document,
  ) {}

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
