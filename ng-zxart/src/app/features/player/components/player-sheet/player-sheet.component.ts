import {Component, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormBuilder, FormGroup, ReactiveFormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonToggleModule} from '@angular/material/button-toggle';
import {MatSliderModule} from '@angular/material/slider';
import {MatExpansionModule} from '@angular/material/expansion';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatInputModule} from '@angular/material/input';
import {MatSelectModule} from '@angular/material/select';
import {Subscription} from 'rxjs';
import {PlayerService} from '../../services/player.service';
import {PlayerState} from '../../models/player-state';
import {RadioCriteria} from '../../models/radio-criteria';
import {RadioPreset} from '../../models/radio-preset';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxBodyDirective} from '../../../../shared/directives/typography/typography.directives';
import {RadioPresetCriteriaService} from '../../services/radio-preset-criteria.service';

type PresetValue = RadioPreset | 'custom';

@Component({
  selector: 'zx-player-sheet',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    MatButtonModule,
    MatIconModule,
    MatButtonToggleModule,
    MatSliderModule,
    MatExpansionModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    ZxPanelComponent,
    ZxStackComponent,
    ZxBodyDirective,
  ],
  templateUrl: './player-sheet.component.html',
  styleUrls: ['./player-sheet.component.scss'],
})
export class PlayerSheetComponent implements OnDestroy {
  state$ = this.playerService.state$;
  form: FormGroup;
  private subscriptions = new Subscription();

  presets: {value: PresetValue; label: string}[] = [
    {value: 'custom', label: 'player.preset.custom'},
    {value: 'discover', label: 'player.preset.discover'},
    {value: 'randomgood', label: 'player.preset.randomgood'},
    {value: 'games', label: 'player.preset.games'},
    {value: 'demoscene', label: 'player.preset.demoscene'},
    {value: 'lastyear', label: 'player.preset.lastyear'},
    {value: 'ay', label: 'player.preset.ay'},
    {value: 'beeper', label: 'player.preset.beeper'},
    {value: 'exotic', label: 'player.preset.exotic'},
    {value: 'underground', label: 'player.preset.underground'},
  ];

  constructor(
    private playerService: PlayerService,
    private presetCriteriaService: RadioPresetCriteriaService,
    private fb: FormBuilder,
  ) {
    this.form = this.fb.group({
      preset: ['custom'],
      minRating: [''],
      maxRating: [''],
      yearsInclude: [''],
      yearsExclude: [''],
      countriesInclude: [''],
      countriesExclude: [''],
      formatGroupsInclude: [''],
      formatGroupsExclude: [''],
      formatsInclude: [''],
      formatsExclude: [''],
    });

    this.setFormFromCriteria(this.playerService.getCriteria(), this.playerService.getPreset());
    const fieldsToWatch = [
      'minRating',
      'maxRating',
      'yearsInclude',
      'yearsExclude',
      'countriesInclude',
      'countriesExclude',
      'formatGroupsInclude',
      'formatGroupsExclude',
      'formatsInclude',
      'formatsExclude',
    ];

    for (const field of fieldsToWatch) {
      this.subscriptions.add(
        this.form.get(field)?.valueChanges.subscribe(() => {
          if (this.form.get('preset')?.value !== 'custom') {
            this.form.get('preset')?.setValue('custom', {emitEvent: false});
          }
        }) ?? new Subscription()
      );
    }
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

  close(): void {
    this.playerService.closePlayer();
  }

  seek(event: Event, duration: number): void {
    const value = Number((event.target as HTMLInputElement).value);
    if (!Number.isFinite(value) || duration <= 0) {
      return;
    }
    this.playerService.seekToPercent(value / duration);
  }

  setRepeatMode(mode: string): void {
    if (mode === 'off' || mode === 'one' || mode === 'all') {
      this.playerService.setRepeatMode(mode);
    }
  }

  toggleShuffle(enabled: boolean): void {
    this.playerService.setShuffleEnabled(enabled);
  }

  applyCriteria(): void {
    const criteria = this.buildCriteriaFromForm();
    this.playerService.startRadio(criteria, null);
  }

  applyPreset(): void {
    const preset = this.form.get('preset')?.value as PresetValue;
    if (!preset || preset === 'custom') {
      return;
    }
    const criteria = this.buildCriteriaFromPreset(preset);
    this.setFormFromCriteria(criteria, preset);
    this.playerService.startRadio(criteria, preset);
  }

  getTitle(authors: string[], title: string): string {
    if (!authors.length) {
      return title;
    }
    return `${authors.join(', ')} - ${title}`;
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

  private buildCriteriaFromForm(): RadioCriteria {
    return {
      minRating: this.toOptionalNumber(this.form.get('minRating')?.value),
      maxRating: this.toOptionalNumber(this.form.get('maxRating')?.value),
      yearsInclude: this.toNumberList(this.form.get('yearsInclude')?.value),
      yearsExclude: this.toNumberList(this.form.get('yearsExclude')?.value),
      countriesInclude: this.toNumberList(this.form.get('countriesInclude')?.value),
      countriesExclude: this.toNumberList(this.form.get('countriesExclude')?.value),
      formatGroupsInclude: this.toStringList(this.form.get('formatGroupsInclude')?.value),
      formatGroupsExclude: this.toStringList(this.form.get('formatGroupsExclude')?.value),
      formatsInclude: this.toStringList(this.form.get('formatsInclude')?.value),
      formatsExclude: this.toStringList(this.form.get('formatsExclude')?.value),
      bestVotesLimit: null,
      maxPlays: null,
      minPartyPlace: null,
      requireGame: null,
      notVotedByUserId: null,
    };
  }

  private buildCriteriaFromPreset(preset: RadioPreset): RadioCriteria {
    return this.presetCriteriaService.buildCriteria(preset);
  }

  private setFormFromCriteria(criteria: RadioCriteria, preset: RadioPreset | null): void {
    this.form.patchValue(
      {
        preset: preset ?? 'custom',
        minRating: this.toInputValue(criteria.minRating),
        maxRating: this.toInputValue(criteria.maxRating),
        yearsInclude: this.joinList(criteria.yearsInclude),
        yearsExclude: this.joinList(criteria.yearsExclude),
        countriesInclude: this.joinList(criteria.countriesInclude),
        countriesExclude: this.joinList(criteria.countriesExclude),
        formatGroupsInclude: this.joinList(criteria.formatGroupsInclude),
        formatGroupsExclude: this.joinList(criteria.formatGroupsExclude),
        formatsInclude: this.joinList(criteria.formatsInclude),
        formatsExclude: this.joinList(criteria.formatsExclude),
      },
      {emitEvent: false},
    );
  }

  private toNumberList(value: string | null | undefined): number[] {
    if (!value) {
      return [];
    }
    return value
      .split(',')
      .map(item => Number(item.trim()))
      .filter(item => Number.isFinite(item));
  }

  private toStringList(value: string | null | undefined): string[] {
    if (!value) {
      return [];
    }
    return value
      .split(',')
      .map(item => item.trim())
      .filter(item => item.length > 0);
  }

  private toOptionalNumber(value: string | null | undefined): number | null {
    if (value === null || value === undefined || value === '') {
      return null;
    }
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  private toInputValue(value: number | null): string {
    return value === null ? '' : String(value);
  }

  private joinList(items: Array<number | string>): string {
    if (!items.length) {
      return '';
    }
    return items.join(', ');
  }
}
