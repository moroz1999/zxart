import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  Input,
  numberAttribute,
  OnChanges,
  OnDestroy,
  OnInit,
  SimpleChanges
} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {catchError, debounceTime, distinctUntilChanged, map, of, Subject, Subscription, switchMap} from 'rxjs';
import {
  TagsQuickFormEditorComponent
} from '../../../../shared/lib/tags-quick-form-editor/tags-quick-form-editor.component';
import {TagItem} from '../../../../shared/models/tag-item';
import {Tag} from '../../../../shared/models/tag';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {TagsSearchService} from '../../../../shared/services/tags-search.service';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {
  ZxTextSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-text-skeleton/zx-text-skeleton.component';
import {ElementPrivilegesApiService} from '../../../../shared/services/element-privileges-api.service';
import {TagsApiService} from '../../services/tags-api.service';
import {TagsPayloadDto} from '../../models/tags-payload.dto';

@Component({
  selector: 'zx-tags-quick-form,zx-tags-quick-form-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    TagsQuickFormEditorComponent,
    ZxButtonComponent,
    ZxPanelComponent,
    ZxTextSkeletonComponent,
  ],
  templateUrl: './tags-quick-form.component.html',
  styleUrl: './tags-quick-form.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TagsQuickFormComponent implements OnChanges, OnDestroy, OnInit {
  @Input({transform: numberAttribute}) elementId = 0;

  hasPrivilege: boolean | null = null;
  hasLoadedData = false;
  loadingData = false;
  saving = false;
  searchLoading = false;
  loadErrorMessage = '';
  saveErrorMessage = '';
  selectedTags: TagItem[] = [];
  suggestedTags: TagItem[] = [];
  searchResults: TagItem[] = [];

  private readonly searchQuerySubject = new Subject<string>();
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly cdr: ChangeDetectorRef,
    private readonly translate: TranslateService,
    private readonly tagsApiService: TagsApiService,
    private readonly tagsSearchService: TagsSearchService,
    private readonly elementPrivilegesApiService: ElementPrivilegesApiService,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(
      this.searchQuerySubject.pipe(
        debounceTime(250),
        distinctUntilChanged(),
        switchMap(query => {
          const normalizedQuery = query.trim();
          if (normalizedQuery === '') {
            this.searchLoading = false;
            this.searchResults = [];
            this.cdr.markForCheck();
            return of([] as TagItem[]);
          }

          this.searchLoading = true;
          this.cdr.markForCheck();

          return this.tagsSearchService.search(normalizedQuery).pipe(
            map(tags => this.mapSearchTags(tags)),
            catchError(() => of([] as TagItem[])),
          );
        }),
      ).subscribe(searchResults => {
        this.searchLoading = false;
        this.searchResults = this.filterSelectedTags(searchResults);
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (!changes['elementId']) {
      return;
    }

    this.resetState();
    if (this.elementId > 0) {
      this.requestPrivileges();
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onInViewport(): void {
    if (this.hasPrivilege !== true || this.hasLoadedData === true || this.loadingData === true) {
      return;
    }

    this.loadTags();
  }

  onSelectedTagsChanged(tags: TagItem[]): void {
    this.selectedTags = [...tags];
    this.searchResults = this.filterSelectedTags(this.searchResults);
    this.cdr.markForCheck();
  }

  onSearchQueryChanged(query: string): void {
    if (query.trim() === '') {
      this.searchLoading = false;
      this.searchResults = [];
      this.cdr.markForCheck();
    }

    this.searchQuerySubject.next(query);
  }

  onRetryLoad(): void {
    this.loadTags();
  }

  onSaveRequested(tags: TagItem[]): void {
    this.saving = true;
    this.saveErrorMessage = '';
    this.cdr.markForCheck();

    this.subscriptions.add(
      this.tagsApiService.saveTags(this.elementId, tags).subscribe({
        next: response => {
          this.applyTagsPayload(response);
          this.saving = false;
          this.searchLoading = false;
          this.searchResults = [];
          this.saveErrorMessage = '';
          this.cdr.markForCheck();
        },
        error: error => {
          this.saving = false;
          this.saveErrorMessage = this.getErrorMessage(error, 'tags-quick-form.save-error');
          this.cdr.markForCheck();
        },
      }),
    );
  }

  private requestPrivileges(): void {
    this.subscriptions.add(
      this.elementPrivilegesApiService.getPrivileges(this.elementId, ['submitTags']).pipe(
        catchError(() => of({submitTags: false})),
      ).subscribe(privileges => {
        this.hasPrivilege = privileges['submitTags'] === true;
        this.cdr.markForCheck();
      }),
    );
  }

  private loadTags(): void {
    this.loadingData = true;
    this.loadErrorMessage = '';
    this.cdr.markForCheck();

    this.subscriptions.add(
      this.tagsApiService.getTags(this.elementId).subscribe({
        next: response => {
          this.applyTagsPayload(response);
          this.loadingData = false;
          this.hasLoadedData = true;
          this.loadErrorMessage = '';
          this.cdr.markForCheck();
        },
        error: error => {
          this.loadingData = false;
          this.loadErrorMessage = this.getErrorMessage(error, 'tags-quick-form.load-error');
          this.cdr.markForCheck();
        },
      }),
    );
  }

  private applyTagsPayload(payload: TagsPayloadDto): void {
    this.selectedTags = [...payload.tags];
    this.suggestedTags = [...payload.suggestedTags];
  }

  private mapSearchTags(tags: Tag[]): TagItem[] {
    return tags.map(tag => ({
      id: Number.isFinite(tag.id) ? tag.id : null,
      title: tag.title,
      description: tag.description || null,
    }));
  }

  private filterSelectedTags(tags: TagItem[]): TagItem[] {
    const selectedTitles = new Set(this.selectedTags.map(tag => this.normalizeTagTitle(tag.title)));

    return tags.filter(tag => selectedTitles.has(this.normalizeTagTitle(tag.title)) === false);
  }

  private normalizeTagTitle(title: string): string {
    return title.trim().toLocaleLowerCase();
  }

  private resetState(): void {
    this.hasPrivilege = null;
    this.hasLoadedData = false;
    this.loadingData = false;
    this.saving = false;
    this.searchLoading = false;
    this.loadErrorMessage = '';
    this.saveErrorMessage = '';
    this.selectedTags = [];
    this.suggestedTags = [];
    this.searchResults = [];
  }

  private getErrorMessage(error: unknown, fallbackTranslationKey: string): string {
    if (error instanceof HttpErrorResponse && typeof error.error?.errorMessage === 'string') {
      return error.error.errorMessage;
    }

    return this.translate.instant(fallbackTranslationKey);
  }
}
