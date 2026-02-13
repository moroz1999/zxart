import {Component, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {animate, style, transition, trigger} from '@angular/animations';
import {FormBuilder, FormGroup, ReactiveFormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {MatIconModule} from '@angular/material/icon';
import {debounceTime, Subscription} from 'rxjs';
import {PlayerService} from '../../services/player.service';
import {EMPTY_RADIO_CRITERIA, RadioCriteria} from '../../models/radio-criteria';
import {PlayerState} from '../../models/player-state';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxBodyDirective, ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxSelectComponent, ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxInputRangeComponent} from '../../../../shared/ui/zx-input-range/zx-input-range.component';
import {ZxMinMaxRangeComponent} from '../../../../shared/ui/zx-min-max-range/zx-min-max-range.component';
import {RatingComponent} from '../../../../shared/components/rating/rating.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {
  ZxFilterPickerComponent,
  ZxFilterPickerItem
} from '../../../../shared/ui/zx-filter-picker/zx-filter-picker.component';
import {VoteService} from '../../../../shared/services/vote.service';
import {RadioApiService} from '../../services/radio-api.service';
import {RadioFilterOptionsDto} from '../../models/radio-filter-options';
import {RadioPreset} from '../../models/radio-preset';
import {RadioPresetCriteriaService} from '../../services/radio-preset-criteria.service';
import {ZxCheckboxFieldComponent} from '../../../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {CurrentUserService} from '../../../../shared/services/current-user.service';

const AUTO_APPLY_DEBOUNCE_MS = 500;

type PlaybackMode = 'once' | 'repeat-one' | 'repeat-all' | 'shuffle-all';
type PartyValue = 'any' | 'yes' | 'no';

@Component({
  selector: 'zx-player-sheet',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    MatIconModule,
    ZxPanelComponent,
    ZxBodyDirective,
    ZxCaptionDirective,
    ZxButtonComponent,
    ZxSelectComponent,
    ZxInputComponent,
    ZxInputRangeComponent,
    ZxMinMaxRangeComponent,
    RatingComponent,
    ZxSkeletonComponent,
    ZxFilterPickerComponent,
    ZxCheckboxFieldComponent,
  ],
  templateUrl: './player-sheet.component.html',
  styleUrls: ['./player-sheet.component.scss'],
  animations: [
    trigger('slideUp', [
      transition(':enter', [
        style({transform: 'translateY(100%)'}),
        animate('300ms cubic-bezier(0.34, 1.4, 0.64, 1)', style({transform: 'translateY(0)'})),
      ]),
      transition(':leave', [
        animate('200ms ease-in', style({transform: 'translateY(100%)'})),
      ]),
    ]),
    trigger('expandPanel', [
      transition(':enter', [
        style({opacity: 0, maxHeight: '0', overflow: 'hidden'}),
        animate('250ms cubic-bezier(0.34, 1.2, 0.64, 1)', style({opacity: 1, maxHeight: '500px'})),
      ]),
      transition(':leave', [
        style({overflow: 'hidden'}),
        animate('200ms ease-in', style({opacity: 0, maxHeight: '0'})),
      ]),
    ]),
  ],
})
export class PlayerSheetComponent implements OnDestroy {
  state$ = this.playerService.state$;
  form: FormGroup;
  expanded = false;
  activePreset: RadioPreset | null = null;
  options: RadioFilterOptionsDto | null = null;
  optionsLoading = false;
  optionsError = false;
  yearRangeBounds: {min: number; max: number} | null = null;
  countryItems: ZxFilterPickerItem[] = [];
  formatGroupItems: ZxFilterPickerItem[] = [];
  formatItems: ZxFilterPickerItem[] = [];
  partyOptions: ZxSelectOption[] = [];
  categoryOptions: ZxSelectOption[] = [];
  ratingRange: {min: number | null; max: number | null} | null = null;
  presets: {key: RadioPreset; label: string}[] = [
    {key: 'discover', label: 'player.preset.discover'},
    {key: 'randomgood', label: 'player.preset.randomgood'},
    {key: 'games', label: 'player.preset.games'},
    {key: 'demoscene', label: 'player.preset.demoscene'},
    {key: 'lastyear', label: 'player.preset.lastyear'},
    {key: 'ay', label: 'player.preset.ay'},
    {key: 'beeper', label: 'player.preset.beeper'},
    {key: 'exotic', label: 'player.preset.exotic'},
    {key: 'underground', label: 'player.preset.underground'},
  ];

  private subscriptions = new Subscription();
  private suppressAutoApply = false;
  private currentCriteria: RadioCriteria = EMPTY_RADIO_CRITERIA;

  constructor(
    private playerService: PlayerService,
    private voteService: VoteService,
    private radioApiService: RadioApiService,
    private translateService: TranslateService,
    private presetCriteriaService: RadioPresetCriteriaService,
    private currentUserService: CurrentUserService,
    private fb: FormBuilder,
  ) {
    this.form = this.fb.group({
      minRating: [''],
      category: [''],
      minPartyPlace: [0],
      yearRange: this.fb.control({min: 0, max: 0}),
      countries: [[]],
      formatGroups: [[]],
      formats: [[]],
      party: ['any'],
      unvotedOnly: [false],
    });

    this.setFormFromCriteria(this.playerService.getCriteria());
    this.buildCategoryOptions(null);

    this.subscriptions.add(
      this.playerService.criteria$.subscribe(criteria => {
        this.currentCriteria = criteria;
        this.setFormFromCriteria(criteria);
      })
    );

    this.subscriptions.add(
      this.playerService.preset$.subscribe(preset => {
        this.activePreset = preset;
      })
    );

    this.subscriptions.add(
      this.translateService.onLangChange.subscribe(() => {
        this.buildCategoryOptions(this.options);
        if (this.options) {
          this.buildOptions(this.options);
        }
      })
    );

    this.subscriptions.add(
      this.form.valueChanges
        .pipe(debounceTime(AUTO_APPLY_DEBOUNCE_MS))
        .subscribe(() => {
          if (this.suppressAutoApply) {
            return;
          }
          this.applyCriteriaFromForm();
        })
    );

    this.subscriptions.add(
      this.form.get('party')?.valueChanges.subscribe(value => {
        this.updatePartyPlaceState(value as PartyValue);
      }) ?? new Subscription()
    );

    this.subscriptions.add(
      this.currentUserService.loadUser().subscribe(user => {
        const control = this.form.get('unvotedOnly');
        if (!control) {
          return;
        }
        if (!user || user.userName === 'anonymous') {
          control.disable({emitEvent: false});
        } else {
          control.enable({emitEvent: false});
        }
      })
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  togglePlay(): void {
    this.playerService.togglePlay();
  }

  previous(): void {
    this.playerService.previous();
  }

  next(): void {
    this.playerService.next();
  }

  stop(): void {
    this.playerService.stop();
  }

  close(): void {
    this.playerService.closePlayer();
  }

  toggleExpanded(): void {
    this.expanded = !this.expanded;
    if (this.expanded) {
      this.loadOptions();
    }
  }

  seekByClick(event: MouseEvent, duration: number): void {
    const el = event.currentTarget as HTMLElement | null;
    if (!el || duration <= 0) {
      return;
    }
    const rect = el.getBoundingClientRect();
    const offsetX = event.clientX - rect.left;
    const percent = Math.max(0, Math.min(1, offsetX / rect.width));
    this.playerService.seekToPercent(percent);
  }

  getCurrentTitle(state: PlayerState): string {
    if (state.currentIndex < 0) {
      return '';
    }
    const tune = state.playlist[state.currentIndex];
    if (!tune) {
      return '';
    }
    return this.getTitle(
      tune.authors.map(author => author.name),
      tune.title,
    );
  }

  getProgressPercent(state: PlayerState): string {
    if (!state.duration) {
      return '0%';
    }
    const percent = Math.max(0, Math.min(1, state.currentTime / state.duration)) * 100;
    return `${percent.toFixed(2)}%`;
  }

  getPlaybackMode(state: PlayerState): PlaybackMode {
    if (state.shuffleEnabled) {
      return 'shuffle-all';
    }
    if (state.repeatMode === 'one') {
      return 'repeat-one';
    }
    if (state.repeatMode === 'all') {
      return 'repeat-all';
    }
    return 'once';
  }

  getPlaybackModeIcon(state: PlayerState): string {
    const mode = this.getPlaybackMode(state);
    switch (mode) {
      case 'repeat-one':
        return 'repeat_one';
      case 'repeat-all':
        return 'repeat';
      case 'shuffle-all':
        return 'shuffle';
      default:
        return 'play_circle';
    }
  }

  cyclePlaybackMode(state: PlayerState): void {
    const mode = this.getPlaybackMode(state);
    const nextMode: PlaybackMode = mode === 'once'
      ? 'repeat-one'
      : mode === 'repeat-one'
        ? 'repeat-all'
        : mode === 'repeat-all'
          ? 'shuffle-all'
          : 'once';

    this.playerService.setPlaybackMode(nextMode);
  }

  applyPreset(preset: RadioPreset): void {
    this.subscriptions.add(
      this.presetCriteriaService.buildCriteria(preset).subscribe((criteria) => {
        this.setFormFromCriteria(criteria);
        this.playerService.startRadio(criteria, preset);
      })
    );
  }

  openCurrentTuneUrl(): void {
    const tune = this.playerService.currentTune;
    if (!tune?.url) {
      return;
    }
    window.open(tune.url, '_blank');
  }

  vote(value: number): void {
    const tune = this.playerService.currentTune;
    if (!tune) {
      return;
    }
    this.voteService.send<'zxMusic'>(tune.id, value, 'zxMusic').subscribe(votes => {
      this.playerService.updateCurrentTune({
        votes,
        userVote: value,
      });
    });
  }

  private loadOptions(): void {
    if (this.optionsLoading || this.options) {
      return;
    }

    this.optionsLoading = true;
    this.optionsError = false;
    this.subscriptions.add(
      this.radioApiService.getFilterOptions().subscribe({
        next: options => {
          this.options = options;
          this.optionsLoading = false;
          this.buildOptions(options);
        },
        error: () => {
          this.optionsLoading = false;
          this.optionsError = true;
        },
      })
    );
  }

  private buildOptions(options: RadioFilterOptionsDto): void {
    this.yearRangeBounds = this.buildYearRangeBounds(options.yearRange.min, options.yearRange.max);
    this.ratingRange = options.ratingRange;
    this.countryItems = options.countries.map(country => ({
      id: String(country.id),
      label: country.title,
    }));
    this.buildCategoryOptions(options);
    this.formatGroupItems = options.formatGroups.map(group => ({
      id: group,
      label: this.getFormatGroupLabel(group),
    }));
    this.formatItems = options.formats.map(format => ({
      id: format,
      label: format,
    }));
    this.partyOptions = options.partyOptions.map(option => ({
      value: option,
      label: this.translateService.instant(`player.filters.party.${option}`),
    }));
    this.setFormFromCriteria(this.currentCriteria);
  }

  private buildCategoryOptions(options: RadioFilterOptionsDto | null): void {
    const base = {
      value: '',
      label: this.translateService.instant('player.filters.category.any'),
    };

    if (!options) {
      this.categoryOptions = [base];
      return;
    }

    this.categoryOptions = [
      base,
      ...options.categories.map(category => ({
        value: String(category.id),
        label: category.title,
      })),
    ];
  }

  private getFormatGroupLabel(group: string): string {
    const key = `player.formatGroup.${group}`;
    const translated = this.translateService.instant(key);
    return translated === key ? group : translated;
  }

  private buildYearRangeBounds(min: number | null, max: number | null): {min: number; max: number} | null {
    if (!min || !max) {
      return null;
    }
    return {min, max};
  }

  private normalizeYearRange(range: {min: number | null; max: number | null}): {min: number; max: number} {
    if (!this.yearRangeBounds) {
      return {min: range.min ?? 0, max: range.max ?? 0};
    }
    const min = range.min ?? this.yearRangeBounds.min;
    const max = range.max ?? this.yearRangeBounds.max;
    return {min, max};
  }

  private setFormFromCriteria(criteria: RadioCriteria): void {
    this.suppressAutoApply = true;
    const yearRange = this.getYearRangeFromCriteria(criteria.yearsInclude);

    this.form.patchValue(
      {
        minRating: this.formatRatingValue(criteria.minRating),
        category: this.getCategorySelection(criteria),
        minPartyPlace: criteria.minPartyPlace ?? 0,
        yearRange: this.normalizeYearRange(yearRange),
        countries: criteria.countriesInclude.map(value => String(value)),
        formatGroups: criteria.formatGroupsInclude,
        formats: criteria.formatsInclude,
        party: this.toPartyValue(criteria.hasParty),
        unvotedOnly: criteria.notVotedByUserId !== null,
      },
      {emitEvent: false},
    );
    this.updatePartyPlaceState(this.form.get('party')?.value as PartyValue);
    this.suppressAutoApply = false;
  }

  private applyCriteriaFromForm(): void {
    const criteria = this.buildCriteriaFromForm();
    this.playerService.startRadio(criteria, null);
  }

  private buildCriteriaFromForm(): RadioCriteria {
    const minRating = this.toOptionalFloat(this.form.get('minRating')?.value);
    const categoryId = this.toOptionalInt(this.form.get('category')?.value);
    const minPartyPlace = this.toOptionalInt(this.form.get('minPartyPlace')?.value);
    const yearRange = this.form.get('yearRange')?.value as {min?: number; max?: number} | null;
    const yearFrom = this.toOptionalInt(yearRange?.min);
    const yearTo = this.toOptionalInt(yearRange?.max);
    const yearsInclude = this.isFullYearRange(yearFrom, yearTo)
      ? []
      : this.buildYearRange(yearFrom, yearTo);
    const unvotedOnly = Boolean(this.form.get('unvotedOnly')?.value);
    const userId = unvotedOnly ? this.currentUserService.userId : null;
    const notVotedByUserId = userId ?? (unvotedOnly ? this.currentCriteria.notVotedByUserId : null);

    return {
      ...EMPTY_RADIO_CRITERIA,
      minRating,
      yearsInclude,
      prodCategoriesInclude: categoryId === null ? [] : [categoryId],
      countriesInclude: this.toNumberList(this.form.get('countries')?.value),
      formatGroupsInclude: this.toStringList(this.form.get('formatGroups')?.value),
      formatsInclude: this.toStringList(this.form.get('formats')?.value),
      minPartyPlace: minPartyPlace && minPartyPlace > 0 ? minPartyPlace : null,
      hasParty: this.toPartyCriteria(this.form.get('party')?.value as PartyValue),
      notVotedByUserId,
    };
  }

  onCountriesChange(ids: string[]): void {
    this.form.get('countries')?.setValue(ids);
  }

  onFormatGroupsChange(ids: string[]): void {
    this.form.get('formatGroups')?.setValue(ids);
  }

  onFormatsChange(ids: string[]): void {
    this.form.get('formats')?.setValue(ids);
  }

  get selectedCountries(): string[] {
    return this.form.get('countries')?.value ?? [];
  }

  get selectedFormatGroups(): string[] {
    return this.form.get('formatGroups')?.value ?? [];
  }

  get selectedFormats(): string[] {
    return this.form.get('formats')?.value ?? [];
  }

  get hasRatingRange(): boolean {
    return this.ratingRange?.min !== null && this.ratingRange?.max !== null;
  }

  get ratingMin(): number {
    return this.ratingRange?.min ?? 0;
  }

  get ratingMax(): number {
    return this.ratingRange?.max ?? 5;
  }

  get hasYearRange(): boolean {
    return this.yearRangeBounds !== null;
  }

  get yearMin(): number {
    return this.yearRangeBounds?.min ?? 0;
  }

  get yearMax(): number {
    return this.yearRangeBounds?.max ?? 0;
  }

  get yearRangeValue(): {min: number; max: number} {
    const value = this.form.get('yearRange')?.value as {min?: number; max?: number} | null;
    if (!value) {
      return {min: this.yearMin, max: this.yearMax};
    }
    return {
      min: value.min ?? this.yearMin,
      max: value.max ?? this.yearMax,
    };
  }

  private getTitle(authors: string[], title: string): string {
    if (!authors.length) {
      return title;
    }
    return `${authors.join(', ')} - ${title}`;
  }

  private updatePartyPlaceState(value: PartyValue | null): void {
    const minPartyPlace = this.form.get('minPartyPlace');
    if (!minPartyPlace) {
      return;
    }
    if (value === 'no') {
      minPartyPlace.disable({emitEvent: false});
      minPartyPlace.setValue(0, {emitEvent: false});
      return;
    }
    minPartyPlace.enable({emitEvent: false});
  }

  private getYearRangeFromCriteria(years: number[]): {min: number | null; max: number | null} {
    if (!years.length) {
      return {min: null, max: null};
    }
    const min = Math.min(...years);
    const max = Math.max(...years);
    return {min, max};
  }

  private buildYearRange(yearFrom: number | null, yearTo: number | null): number[] {
    if (!yearFrom && !yearTo) {
      return [];
    }
    const minYear = yearFrom ?? yearTo;
    const maxYear = yearTo ?? yearFrom;
    if (!minYear || !maxYear) {
      return [];
    }
    const start = Math.min(minYear, maxYear);
    const end = Math.max(minYear, maxYear);
    const years: number[] = [];
    for (let year = start; year <= end; year += 1) {
      years.push(year);
    }
    return years;
  }

  private isFullYearRange(yearFrom: number | null, yearTo: number | null): boolean {
    if (!this.yearRangeBounds) {
      return false;
    }
    if (yearFrom === null || yearTo === null) {
      return false;
    }
    const min = Math.min(yearFrom, yearTo);
    const max = Math.max(yearFrom, yearTo);
    return min === this.yearRangeBounds.min && max === this.yearRangeBounds.max;
  }

  private toNumberList(value: string[] | string | null | undefined): number[] {
    if (!value) {
      return [];
    }
    const items = Array.isArray(value) ? value : [value];
    return items
      .map(item => Number(item))
      .filter(item => Number.isFinite(item));
  }

  private toStringList(value: string[] | string | null | undefined): string[] {
    if (!value) {
      return [];
    }
    const items = Array.isArray(value) ? value : [value];
    return items
      .map(item => item.trim())
      .filter(item => item.length > 0);
  }

  private toOptionalInt(value: string | number | null | undefined): number | null {
    if (value === null || value === undefined || value === '') {
      return null;
    }
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  private toOptionalFloat(value: string | null | undefined): number | null {
    if (!value) {
      return null;
    }
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  private toPartyValue(value: boolean | null): PartyValue {
    if (value === true) {
      return 'yes';
    }
    if (value === false) {
      return 'no';
    }
    return 'any';
  }

  private toPartyCriteria(value: PartyValue): boolean | null {
    if (value === 'yes') {
      return true;
    }
    if (value === 'no') {
      return false;
    }
    return null;
  }

  private getCategorySelection(criteria: RadioCriteria): string {
    const firstCategory = criteria.prodCategoriesInclude[0];
    if (!firstCategory) {
      return '';
    }
    return String(firstCategory);
  }

  private formatRatingValue(value: number | null): string {
    if (value === null) {
      return '';
    }
    return value.toFixed(1);
  }

  resetFilters(): void {
    this.setFormFromCriteria(EMPTY_RADIO_CRITERIA);
    this.playerService.startRadio(EMPTY_RADIO_CRITERIA, null);
  }
}
