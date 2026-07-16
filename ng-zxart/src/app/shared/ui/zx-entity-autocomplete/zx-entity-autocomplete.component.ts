import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, forwardRef, Input, OnDestroy, OnInit} from '@angular/core';
import {ControlValueAccessor, FormsModule, NG_VALUE_ACCESSOR} from '@angular/forms';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged, switchMap, tap} from 'rxjs/operators';
import {EntityRef} from '../../models/entity-ref';
import {EntitySearchService} from '../../services/entity-search.service';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxSpinnerComponent} from '../zx-spinner/zx-spinner.component';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {DropdownPopoverAnimation} from '../../animations/popover-animations';

/**
 * Single-select relation picker (country, city, author, group, party, …) for
 * forms. Implements ControlValueAccessor: the value is an `EntityRef | null`
 * (the form submits `value?.id`). Searches via {@link EntitySearchService}.
 */
@Component({
  selector: 'zx-entity-autocomplete',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxInputComponent,
    ZxSpinnerComponent,
    ZxButtonComponent,
  ],
  templateUrl: './zx-entity-autocomplete.component.html',
  styleUrl: './zx-entity-autocomplete.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [DropdownPopoverAnimation],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxEntityAutocompleteComponent),
      multi: true,
    },
  ],
})
export class ZxEntityAutocompleteComponent implements OnInit, OnDestroy, ControlValueAccessor {
  /** Comma-separated structureTypes to search, e.g. `country` or `author,authorAlias`. */
  @Input({required: true}) types!: string;
  @Input() placeholder = '';

  value: EntityRef | null = null;
  disabled = false;
  query = '';
  searchResults: EntityRef[] = [];
  loading = false;

  readonly positions: ConnectedPosition[] = [
    {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 4},
    {originX: 'start', originY: 'top', overlayX: 'start', overlayY: 'bottom', offsetY: -4},
  ];

  private readonly querySubject = new Subject<string>();
  private readonly subscriptions = new Subscription();
  private onChange: (value: EntityRef | null) => void = () => undefined;
  private onTouched: () => void = () => undefined;

  constructor(
    private readonly entitySearch: EntitySearchService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(
      this.querySubject.pipe(
        debounceTime(250),
        distinctUntilChanged(),
        tap(() => {
          this.loading = true;
          this.cdr.markForCheck();
        }),
        switchMap(query => this.entitySearch.search(this.types, query)),
      ).subscribe(results => {
        this.searchResults = results;
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  get dropdownOpen(): boolean {
    return !this.disabled && this.query.trim() !== '' && (this.loading || this.searchResults.length > 0);
  }

  onQueryChange(value: string): void {
    this.query = value;
    this.querySubject.next(value);
  }

  onSelect(ref: EntityRef): void {
    this.value = ref;
    this.clearQuery();
    this.onChange(ref);
    this.onTouched();
  }

  onClear(): void {
    this.value = null;
    this.onChange(null);
    this.onTouched();
  }

  closeDropdown(): void {
    this.clearQuery();
  }

  writeValue(value: EntityRef | null): void {
    this.value = value;
    this.cdr.markForCheck();
  }

  registerOnChange(fn: (value: EntityRef | null) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    this.cdr.markForCheck();
  }

  private clearQuery(): void {
    this.query = '';
    this.searchResults = [];
  }
}
