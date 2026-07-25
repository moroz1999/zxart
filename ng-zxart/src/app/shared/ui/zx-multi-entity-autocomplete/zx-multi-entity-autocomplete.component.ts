import {CommonModule} from '@angular/common';
import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  forwardRef,
  Input,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {ControlValueAccessor, FormsModule, NG_VALUE_ACCESSOR} from '@angular/forms';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged, switchMap, tap} from 'rxjs/operators';
import {EntityRef} from '../../models/entity-ref';
import {ChipItem} from '../../models/chip-item';
import {EntitySearchService} from '../../services/entity-search.service';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxSpinnerComponent} from '../zx-spinner/zx-spinner.component';
import {ZxChipsComponent} from '../zx-chips/zx-chips.component';
import {DropdownPopoverAnimation} from '../../animations/popover-animations';
import {listKeyboardNav} from '../../utils/list-keyboard-nav';

/**
 * Multi-select relation picker (groups, publishers, compilation/series prods, …)
 * for forms. Implements ControlValueAccessor: the value is an `EntityRef[]`
 * (the form submits the list of `id`s).
 *
 * Self-contained: it owns the search input and an overlay results list with
 * keyboard navigation (ArrowUp/ArrowDown/Enter/Escape), and renders the picked
 * items as removable {@link ZxChipsComponent} chips. Already-picked and
 * duplicate ids never appear in the results. This does NOT wrap the single
 * {@link ZxEntityAutocompleteComponent}, so a pick lands only in the chip row —
 * there is no second "selected" display inside the input.
 */
@Component({
  selector: 'zx-multi-entity-autocomplete',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxInputComponent,
    ZxSpinnerComponent,
    ZxChipsComponent,
  ],
  templateUrl: './zx-multi-entity-autocomplete.component.html',
  styleUrl: './zx-multi-entity-autocomplete.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [DropdownPopoverAnimation],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxMultiEntityAutocompleteComponent),
      multi: true,
    },
  ],
})
export class ZxMultiEntityAutocompleteComponent implements OnInit, OnDestroy, ControlValueAccessor {
  /** Comma-separated structureTypes to search, e.g. `group` or `zxProd,zxRelease`. */
  @Input({required: true}) types!: string;
  @Input() placeholder = '';

  @ViewChild('search') private readonly searchEl?: ElementRef<HTMLElement>;

  items: EntityRef[] = [];
  chips: ChipItem[] = [];
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
  private onChange: (value: EntityRef[]) => void = () => undefined;
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
        this.activeIndex = 0;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  /** Search hits not already picked — the only rows the dropdown offers. */
  get visibleResults(): EntityRef[] {
    return this.searchResults.filter(result => !this.items.some(item => item.id === result.id));
  }

  get dropdownOpen(): boolean {
    return !this.disabled && this.query.trim() !== '' && (this.loading || this.visibleResults.length > 0);
  }

  get overlayWidth(): number {
    // 0 lets the dropdown fall back to its CSS min-width before the trigger is measured.
    return this.searchEl?.nativeElement.offsetWidth ?? 0;
  }

  onQueryChange(value: string): void {
    this.query = value;
    this.querySubject.next(value);
  }

  onKeydown(event: KeyboardEvent): void {
    if (!this.dropdownOpen) {
      return;
    }
    const results = this.visibleResults;
    const action = listKeyboardNav(event.key, this.activeIndex, results.length);
    switch (action.kind) {
      case 'move':
        event.preventDefault();
        this.activeIndex = action.index;
        break;
      case 'select': {
        // Prevent the surrounding form from submitting on selection.
        event.preventDefault();
        const active = results[action.index];
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
    if (!this.items.some(item => item.id === ref.id)) {
      this.items = [...this.items, ref];
      this.syncChips();
      this.emit();
    }
    this.clearQuery();
  }

  onRemoveChip(chip: ChipItem): void {
    const index = this.chips.indexOf(chip);
    if (index === -1) {
      return;
    }
    this.items = this.items.filter((_, i) => i !== index);
    this.syncChips();
    this.emit();
  }

  closeDropdown(): void {
    this.clearQuery();
  }

  writeValue(value: EntityRef[] | null): void {
    this.items = value ? [...value] : [];
    this.syncChips();
    this.cdr.markForCheck();
  }

  registerOnChange(fn: (value: EntityRef[]) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    this.cdr.markForCheck();
  }

  private syncChips(): void {
    // Rebuilt as a stable array so onRemoveChip can map a chip back to its index.
    this.chips = this.items.map(item => ({title: item.title}));
  }

  private clearQuery(): void {
    this.query = '';
    this.searchResults = [];
    this.activeIndex = 0;
    this.cdr.markForCheck();
  }

  private emit(): void {
    this.onChange(this.items);
    this.onTouched();
  }
}
