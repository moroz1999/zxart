import {Injectable} from '@angular/core';
import {BehaviorSubject, EMPTY, Observable} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {PreferenceValues} from '../../settings/models/preference.dto';
import {UserPreferencesService} from '../../settings/services/user-preferences.service';
import {
  PICTURE_SCALE_DEFAULT,
  PICTURE_SCALE_VALUES,
  PICTURE_SETTINGS_DEFAULTS,
  PictureMode,
  PictureScale,
  PictureSettings,
  PREF_CODE_PICTURE_BORDER,
  PREF_CODE_PICTURE_HIDDEN,
  PREF_CODE_PICTURE_MODE,
  PREF_CODE_PICTURE_SCALE,
} from '../models/picture-settings';

@Injectable({
  providedIn: 'root',
})
export class PictureSettingsService {
  private readonly settings$ = new BehaviorSubject<PictureSettings>(PICTURE_SETTINGS_DEFAULTS);
  private readonly scale$ = new BehaviorSubject<PictureScale>(PICTURE_SCALE_DEFAULT);

  constructor(private userPreferencesService: UserPreferencesService) {
    this.applyPreferences(this.userPreferencesService.getPreferences());
    this.userPreferencesService.preferences$.subscribe(preferences => this.applyPreferences(preferences));
  }

  get settings(): Observable<PictureSettings> {
    return this.settings$.asObservable();
  }

  get currentSettings(): PictureSettings {
    return this.settings$.value;
  }

  /**
   * Kept apart from `settings` because it changes how the picture is laid out,
   * not how it is rendered — it takes no part in building the image URL.
   */
  get scale(): Observable<PictureScale> {
    return this.scale$.asObservable();
  }

  get currentScale(): PictureScale {
    return this.scale$.value;
  }

  setScale(scale: PictureScale): void {
    if (scale === this.scale$.value) {
      return;
    }
    this.savePreference(PREF_CODE_PICTURE_SCALE, scale);
  }

  setMode(mode: PictureMode): void {
    this.savePreference(PREF_CODE_PICTURE_MODE, mode);
  }

  setBorder(border: boolean): void {
    this.savePreference(PREF_CODE_PICTURE_BORDER, border ? '1' : '0');
  }

  setHidden(hidden: boolean): void {
    this.savePreference(PREF_CODE_PICTURE_HIDDEN, hidden ? '1' : '0');
  }

  private savePreference(code: string, value: string): void {
    this.userPreferencesService.setPreference(code, value).pipe(
      catchError(() => EMPTY),
    ).subscribe();
  }

  private applyPreferences(preferences: PreferenceValues): void {
    const settings = this.loadFromPreferences(preferences);
    if (
      settings.mode !== this.settings$.value.mode
      || settings.border !== this.settings$.value.border
      || settings.hidden !== this.settings$.value.hidden
    ) {
      this.settings$.next(settings);
    }

    const scale = this.loadScaleFromPreferences(preferences);
    if (scale !== this.scale$.value) {
      this.scale$.next(scale);
    }
  }

  private loadFromPreferences(preferences: PreferenceValues): PictureSettings {
    const modeRaw = preferences[PREF_CODE_PICTURE_MODE];
    const mode = this.isValidMode(modeRaw) ? modeRaw : PICTURE_SETTINGS_DEFAULTS.mode;
    const border = (preferences[PREF_CODE_PICTURE_BORDER] ?? '1') === '1';
    const hidden = (preferences[PREF_CODE_PICTURE_HIDDEN] ?? '0') === '1';
    return {mode, border, hidden};
  }

  private loadScaleFromPreferences(preferences: PreferenceValues): PictureScale {
    const raw = preferences[PREF_CODE_PICTURE_SCALE];
    return this.isValidScale(raw) ? raw : PICTURE_SCALE_DEFAULT;
  }

  private isValidScale(value: string | undefined): value is PictureScale {
    return PICTURE_SCALE_VALUES.includes(value as PictureScale);
  }

  private isValidMode(value: string | undefined): value is PictureMode {
    return value === 'mix' || value === 'flicker' || value === 'interlace1' || value === 'interlace2';
  }
}
