import {Directive, ElementRef, Inject, inject, OnDestroy, OnInit} from '@angular/core';
import {Observable} from 'rxjs';
import {ModuleSettings} from '../models/firstpage-config';
import {MODULE_SETTINGS} from '../models/module-settings.token';

@Directive()
export abstract class FirstpageModuleBase<T> implements OnInit, OnDestroy {
  items: T[] = [];
  loading = true;
  error = false;

  private el = inject(ElementRef);
  private observer?: IntersectionObserver;

  protected constructor(
    @Inject(MODULE_SETTINGS) protected settings: ModuleSettings
  ) {}

  ngOnInit(): void {
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
