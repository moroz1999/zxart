import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, forwardRef, Input, OnDestroy, OnInit} from '@angular/core';
import {ControlValueAccessor, FormsModule, NG_VALUE_ACCESSOR} from '@angular/forms';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {TranslateModule} from '@ngx-translate/core';
import {Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged, switchMap, tap} from 'rxjs/operators';
import {EntityRef} from '../../models/entity-ref';
import {EntitySearchService} from '../../services/entity-search.service';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxSpinnerComponent} from '../zx-spinner/zx-spinner.component';
import {ZxCloseButtonComponent} from '../zx-close-button/zx-close-button.component';
import {DropdownPopoverAnimation} from '../../animations/popover-animations';
import {listKeyboardNav} from '../../utils/list-keyboard-nav';

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
    TranslateModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxInputComponent,
    ZxSpinnerComponent,
    ZxCloseButtonComponent,
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
  activeIndex = 0;

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
        this.activeIndex = 0;
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

  onKeydown(event: KeyboardEvent): void {
    if (!this.dropdownOpen) {
      return;
    }
    const action = listKeyboardNav(event.key, this.activeIndex, this.searchResults.length);
    switch (action.kind) {
      case 'move':
        event.preventDefault();
        this.activeIndex = action.index;
        break;
      case 'select': {
        // Prevent the surrounding form from submitting on selection.
        event.preventDefault();
        const active = this.searchResults[action.index];
        if (active) {
          this.onSelect(active);
        }
        break;
      }
      case 'close':
        this.closeDropdown();
        break;
    }
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
