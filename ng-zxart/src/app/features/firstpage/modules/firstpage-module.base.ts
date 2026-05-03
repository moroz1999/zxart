import {Directive, ElementRef, Inject, inject, OnDestroy, OnInit} from '@angular/core';
import {BehaviorSubject, combineLatest, Observable, Subscription} from 'rxjs';
import {map} from 'rxjs/operators';
import {ModuleSettings, ModuleType} from '../models/firstpage-config';
import {MODULE_SETTINGS} from '../models/module-settings.token';
import {CatalogueCategory, MODULE_LINK_CONFIG, ModuleLinkConfig} from '../models/firstpage-view-all-links';
import {BackendLinksService} from '../../header/services/backend-links.service';
import {BackendLinks} from '../../header/models/backend-links';

export interface ModuleVm<T> {
  items: T[];
  loading: boolean;
  error: boolean;
  empty: boolean;
  viewAllUrl: string | undefined;
  viewAllLabelKey: string | undefined;
}

interface ModuleState<T> {
  items: T[];
  loading: boolean;
  error: boolean;
}

interface LinkState {
  url: string | undefined;
  labelKey: string | undefined;
}

@Directive()
export abstract class FirstpageModuleBase<T> implements OnInit, OnDestroy {
  protected abstract readonly moduleType: ModuleType;

  private readonly stateStore = new BehaviorSubject<ModuleState<T>>({
    items: [],
    loading: true,
    error: false,
  });

  private readonly linkStore = new BehaviorSubject<LinkState>({
    url: undefined,
    labelKey: undefined,
  });

  readonly vm$: Observable<ModuleVm<T>> = combineLatest([
    this.stateStore,
    this.linkStore,
  ]).pipe(
    map(([state, link]) => ({
      items: state.items,
      loading: state.loading,
      error: state.error,
      empty: !state.loading && !state.error && state.items.length === 0,
      viewAllUrl: link.url,
      viewAllLabelKey: link.labelKey,
    }))
  );

  readonly items$: Observable<T[] | null> = this.stateStore.pipe(
    map(state => state.loading ? null : state.items),
  );

  private el = inject(ElementRef);
  private observer?: IntersectionObserver;
  private backendLinksService = inject(BackendLinksService);
  private subscription = new Subscription();

  protected constructor(
    @Inject(MODULE_SETTINGS) protected settings: ModuleSettings
  ) {}

  protected get currentItems(): T[] {
    return this.stateStore.getValue().items;
  }

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

  ngOnDestroy(): void {
    this.observer?.disconnect();
    this.subscription.unsubscribe();
  }

  private resolveViewAllLink(): void {
    const config: ModuleLinkConfig | null = MODULE_LINK_CONFIG[this.moduleType];
    if (!config) {
      return;
    }

    this.linkStore.next({url: undefined, labelKey: config.titleKey});

    this.subscription.add(
      this.backendLinksService.links$.subscribe(links => {
        const baseUrl = this.getBaseUrlForCategory(links, config.category);
        if (baseUrl) {
          this.linkStore.next({url: baseUrl + config.searchParams, labelKey: config.titleKey});
        }
      })
    );
  }

  private getBaseUrlForCategory(baseUrls: BackendLinks, category: CatalogueCategory): string | null {
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

  private fetchData(): void {
    this.subscription.add(
      this.loadData().subscribe({
        next: items => this.stateStore.next({items, loading: false, error: false}),
        error: () => this.stateStore.next({items: [], loading: false, error: true}),
      })
    );
  }

  protected abstract loadData(): Observable<T[]>;
}
