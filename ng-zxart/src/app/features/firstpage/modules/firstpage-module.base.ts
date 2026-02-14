import {Directive, ElementRef, Inject, inject, OnDestroy, OnInit} from '@angular/core';
import {Observable} from 'rxjs';
import {TranslateService} from '@ngx-translate/core';
import {ModuleSettings, ModuleType} from '../models/firstpage-config';
import {MODULE_SETTINGS} from '../models/module-settings.token';
import {CatalogueCategory, MODULE_LINK_CONFIG} from '../models/firstpage-view-all-links';
import {FirstpageViewAllLinksService} from '../services/firstpage-view-all-links.service';

@Directive()
export abstract class FirstpageModuleBase<T> implements OnInit, OnDestroy {
  items: T[] = [];
  loading = true;
  error = false;
  viewAllUrl?: string;
  viewAllLabel?: string;

  protected abstract readonly moduleType: ModuleType;

  private el = inject(ElementRef);
  private observer?: IntersectionObserver;
  private viewAllLinksService = inject(FirstpageViewAllLinksService);
  private translate = inject(TranslateService);

  protected constructor(
    @Inject(MODULE_SETTINGS) protected settings: ModuleSettings
  ) {}

  ngOnInit(): void {
    this.resolveViewAllLink();

    this.observer = new IntersectionObserver(
      entries => {
        if (entries.some(e => e.isIntersecting)) {
          this.observer?.disconnect();
          this.observer = undefined;
          this.fetchData();
        }
      },
      {rootMargin: '200px'}
    );
    this.observer.observe(this.el.nativeElement);
  }

  private resolveViewAllLink(): void {
    const config = MODULE_LINK_CONFIG[this.moduleType];
    if (!config) {
      return;
    }

    this.viewAllLabel = this.translate.instant(config.titleKey);

    this.viewAllLinksService.getBaseUrls().subscribe(baseUrls => {
      const baseUrl = this.getBaseUrlForCategory(baseUrls, config.category);
      if (baseUrl) {
        this.viewAllUrl = baseUrl + config.searchParams;
      }
    });
  }

  private getBaseUrlForCategory(baseUrls: {prodCatalogueBaseUrl: string | null; graphicsBaseUrl: string | null; musicBaseUrl: string | null}, category: CatalogueCategory): string | null {
    switch (category) {
      case 'zxProd':
      case 'zxRelease':
        return baseUrls.prodCatalogueBaseUrl;
      case 'graphics':
        return baseUrls.graphicsBaseUrl;
      case 'music':
        return baseUrls.musicBaseUrl;
    }
  }

  ngOnDestroy(): void {
    this.observer?.disconnect();
  }

  private fetchData(): void {
    this.loadData().subscribe({
      next: items => {
        this.items = items;
        this.loading = false;
      },
      error: () => {
        this.error = true;
        this.loading = false;
      }
    });
  }

  protected abstract loadData(): Observable<T[]>;
}
