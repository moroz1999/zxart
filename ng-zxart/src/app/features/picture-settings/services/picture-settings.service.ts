import {Injectable} from '@angular/core';
import {BehaviorSubject, Observable} from 'rxjs';
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
    this.userPreferencesService.initialize().subscribe(() => {
      this.settings$.next(this.loadFromPreferences());
      this.scale$.next(this.loadScaleFromPreferences());
    });
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
    this.scale$.next(scale);
    this.userPreferencesService.setPreference(PREF_CODE_PICTURE_SCALE, scale).subscribe();
  }

  setMode(mode: PictureMode): void {
    this.settings$.next({...this.settings$.value, mode});
    this.userPreferencesService.setPreference(PREF_CODE_PICTURE_MODE, mode).subscribe();
  }

  setBorder(border: boolean): void {
    this.settings$.next({...this.settings$.value, border});
    this.userPreferencesService.setPreference(PREF_CODE_PICTURE_BORDER, border ? '1' : '0').subscribe();
  }

  setHidden(hidden: boolean): void {
    this.settings$.next({...this.settings$.value, hidden});
    this.userPreferencesService.setPreference(PREF_CODE_PICTURE_HIDDEN, hidden ? '1' : '0').subscribe();
  }

  private loadFromPreferences(): PictureSettings {
    const modeRaw = this.userPreferencesService.getPreference(PREF_CODE_PICTURE_MODE);
    const mode = this.isValidMode(modeRaw) ? modeRaw : PICTURE_SETTINGS_DEFAULTS.mode;
    const border = (this.userPreferencesService.getPreference(PREF_CODE_PICTURE_BORDER) ?? '1') === '1';
    const hidden = (this.userPreferencesService.getPreference(PREF_CODE_PICTURE_HIDDEN) ?? '0') === '1';
    return {mode, border, hidden};
  }

  private loadScaleFromPreferences(): PictureScale {
    const raw = this.userPreferencesService.getPreference(PREF_CODE_PICTURE_SCALE);
    return this.isValidScale(raw) ? raw : PICTURE_SCALE_DEFAULT;
  }

  private isValidScale(value: string | undefined): value is PictureScale {
    return PICTURE_SCALE_VALUES.includes(value as PictureScale);
  }

  private isValidMode(value: string | undefined): value is PictureMode {
    return value === 'mix' || value === 'flicker' || value === 'interlace1' || value === 'interlace2';
  }
}
