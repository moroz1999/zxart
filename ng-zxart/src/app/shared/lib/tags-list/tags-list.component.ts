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
  SimpleChanges,
} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {catchError, debounceTime, distinctUntilChanged, map, of, Subject, Subscription, switchMap} from 'rxjs';
import {Tag} from '../../models/tag';
import {TagChipItem} from '../../models/tag-chip-item';
import {TagItem} from '../../models/tag-item';
import {ElementPrivilegesApiService} from '../../services/element-privileges-api.service';
import {TagsSearchService} from '../../services/tags-search.service';
import {
  TagsQuickFormEditorComponent
} from '../tags-quick-form-editor/tags-quick-form-editor.component';
import {ZxEditButtonComponent} from '../../ui/zx-edit-button/zx-edit-button.component';
import {LabelDirective} from '../../ui/typography/directives/label.directive';
import {ZxButtonComponent} from '../../ui/zx-button/zx-button.component';
import {ZxInlineComponent} from '../../ui/zx-inline/zx-inline.component';
import {ZxTextSkeletonComponent} from '../../ui/zx-skeleton/components/zx-text-skeleton/zx-text-skeleton.component';
import {ZxStackComponent} from '../../ui/zx-stack/zx-stack.component';
import {ZxTagsChipsComponent} from '../../ui/zx-tags-chips/zx-tags-chips.component';
import {TagsApiService} from './tags-api.service';
import {TagsPayloadDto} from './tags-payload.dto';

@Component({
  selector: 'zx-tags-list',
  standalone: true,
  imports: [
    CommonModule,
    LabelDirective,
    TagsQuickFormEditorComponent,
    TranslateModule,
    ZxButtonComponent,
    ZxEditButtonComponent,
    ZxInlineComponent,
    ZxStackComponent,
    ZxTagsChipsComponent,
    ZxTextSkeletonComponent,
  ],
  templateUrl: './tags-list.component.html',
  styleUrl: './tags-list.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TagsListComponent implements OnChanges, OnDestroy, OnInit {
  @Input({required: true}) set tags(value: ReadonlyArray<TagChipItem>) {
    this.inputTags = [...value];
  }

  @Input({transform: numberAttribute}) elementId = 0;
  @Input() label = '';
  @Input() bordered = false;
  @Input() editAriaLabel = '';

  hasEditPrivilege = false;
  editExpanded = false;
  hasLoadedData = false;
  loadingData = false;
  saving = false;
  searchLoading = false;
  loadErrorMessage = '';
  saveErrorMessage = '';
  selectedTags: TagItem[] = [];
  suggestedTags: TagItem[] = [];
  searchResults: TagItem[] = [];

  private inputTags: TagChipItem[] = [];
  private savedTags: TagChipItem[] | null = null;
  private readonly searchQuerySubject = new Subject<string>();
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly cdr: ChangeDetectorRef,
    private readonly translate: TranslateService,
    private readonly tagsApiService: TagsApiService,
    private readonly tagsSearchService: TagsSearchService,
    private readonly elementPrivilegesApiService: ElementPrivilegesApiService,
  ) {}

  get visibleTags(): ReadonlyArray<TagChipItem> {
    return this.savedTags ?? this.inputTags;
  }

  get shouldRender(): boolean {
    return this.visibleTags.length > 0 || this.hasEditPrivilege;
  }

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

    this.resetEditState();
    if (this.elementId > 0) {
      this.requestPrivileges();
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onEditClick(): void {
    this.editExpanded = !this.editExpanded;

    if (this.editExpanded && this.hasLoadedData === false && this.loadingData === false) {
      this.loadTags();
    }
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
          this.savedTags = response.tags.map(tag => ({title: tag.title}));
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
        this.hasEditPrivilege = privileges['submitTags'] === true;
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

  private resetEditState(): void {
    this.hasEditPrivilege = false;
    this.editExpanded = false;
    this.hasLoadedData = false;
    this.loadingData = false;
    this.saving = false;
    this.searchLoading = false;
    this.loadErrorMessage = '';
    this.saveErrorMessage = '';
    this.selectedTags = [];
    this.suggestedTags = [];
    this.searchResults = [];
    this.savedTags = null;
  }

  private getErrorMessage(error: unknown, fallbackTranslationKey: string): string {
    if (error instanceof HttpErrorResponse && typeof error.error?.errorMessage === 'string') {
      return error.error.errorMessage;
    }

    return this.translate.instant(fallbackTranslationKey);
  }
}
