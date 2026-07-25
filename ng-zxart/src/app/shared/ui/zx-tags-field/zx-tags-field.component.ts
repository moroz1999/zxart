import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, forwardRef, Input, OnDestroy, OnInit} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {of, Subject, Subscription} from 'rxjs';
import {catchError, debounceTime, distinctUntilChanged, map, switchMap, tap} from 'rxjs/operators';
import {TagItem} from '../../models/tag-item';
import {Tag} from '../../models/tag';
import {TagsSearchService} from '../../services/tags-search.service';
import {ZxTagsInputComponent} from '../zx-tags-input/zx-tags-input.component';

/**
 * Reactive-form tag editor: chips + type-to-search autocomplete + "create new
 * tag", the same UX as the public prod page. Implements ControlValueAccessor
 * over the legacy `tagsText` string (comma-joined tag titles), so any form binds
 * it with `<zx-tags-field formControlName="tagsText">`.
 */
@Component({
  selector: 'zx-tags-field',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxTagsInputComponent],
  template: `
    <zx-tags-input
      [tags]="tags"
      [searchResults]="searchResults"
      [searchLoading]="searchLoading"
      [disabled]="disabled"
      [placeholder]="placeholder || ('tags-quick-form.search-placeholder' | translate)"
      [removeButtonAriaLabel]="'tags-quick-form.remove-tag' | translate"
      [addButtonLabel]="'tags-quick-form.add-new' | translate"
      (queryChanged)="onQuery($event)"
      (tagSelected)="addTag($event)"
      (customTagAdded)="addCustomTag($event)"
      (tagRemoved)="removeTag($event)"
    ></zx-tags-input>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxTagsFieldComponent),
      multi: true,
    },
  ],
})
export class ZxTagsFieldComponent implements OnInit, OnDestroy, ControlValueAccessor {
  @Input() placeholder = '';

  tags: TagItem[] = [];
  searchResults: TagItem[] = [];
  searchLoading = false;
  disabled = false;

  private readonly query = new Subject<string>();
  private readonly subscriptions = new Subscription();
  private onChange: (value: string) => void = () => undefined;
  private onTouched: () => void = () => undefined;

  constructor(
    private readonly tagsSearch: TagsSearchService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(
      this.query.pipe(
        debounceTime(250),
        distinctUntilChanged(),
        tap(() => {
          this.searchLoading = true;
          this.cdr.markForCheck();
        }),
        switchMap(query => this.tagsSearch.search(query).pipe(
          map(tags => this.toTagItems(tags)),
          catchError(() => of([] as TagItem[])),
        )),
      ).subscribe(results => {
        this.searchResults = this.excludeSelected(results);
        this.searchLoading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  writeValue(value: string | null): void {
    this.tags = this.parse(value ?? '');
    this.cdr.markForCheck();
  }

  registerOnChange(fn: (value: string) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    this.cdr.markForCheck();
  }

  onQuery(query: string): void {
    if (query.trim() === '') {
      this.searchLoading = false;
      this.searchResults = [];
    }
    this.query.next(query);
  }

  addTag(tag: TagItem): void {
    this.add(tag);
  }

  addCustomTag(title: string): void {
    this.add({id: null, title: title.trim(), description: null});
  }

  removeTag(tag: TagItem): void {
    this.tags = this.tags.filter(existing => this.key(existing) !== this.key(tag));
    this.searchResults = this.excludeSelected(this.searchResults);
    this.emit();
  }

  private add(tag: TagItem): void {
    if (tag.title === '' || this.tags.some(existing => this.key(existing) === this.key(tag))) {
      return;
    }
    this.tags = [...this.tags, tag];
    this.searchResults = this.excludeSelected(this.searchResults);
    this.emit();
  }

  private emit(): void {
    this.onChange(this.tags.map(tag => tag.title).join(', '));
    this.onTouched();
  }

  private parse(text: string): TagItem[] {
    return text
      .split(',')
      .map(title => title.trim())
      .filter(title => title !== '')
      .map(title => ({id: null, title, description: null}));
  }

  private toTagItems(tags: Tag[]): TagItem[] {
    return tags.map(tag => ({
      id: Number.isFinite(tag.id) ? tag.id : null,
      title: tag.title,
      description: tag.description || null,
    }));
  }

  private excludeSelected(results: TagItem[]): TagItem[] {
    const selected = new Set(this.tags.map(tag => this.key(tag)));
    return results.filter(tag => !selected.has(this.key(tag)));
  }

  private key(tag: TagItem): string {
    return tag.title.trim().toLowerCase();
  }
}
