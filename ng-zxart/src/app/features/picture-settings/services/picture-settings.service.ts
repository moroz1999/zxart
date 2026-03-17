import {Injectable} from '@angular/core';
import {BehaviorSubject, Observable} from 'rxjs';
import {UserPreferencesService} from '../../settings/services/user-preferences.service';
import {
  PICTURE_SETTINGS_DEFAULTS,
  PictureMode,
  PictureSettings,
  PREF_CODE_PICTURE_BORDER,
  PREF_CODE_PICTURE_HIDDEN,
  PREF_CODE_PICTURE_MODE,
} from '../models/picture-settings';

@Injectable({
  providedIn: 'root',
})
export class PictureSettingsService {
  private readonly settings$ = new BehaviorSubject<PictureSettings>(PICTURE_SETTINGS_DEFAULTS);

  constructor(private userPreferencesService: UserPreferencesService) {
    this.userPreferencesService.initialize().subscribe(() => {
      this.settings$.next(this.loadFromPreferences());
    });
  }

  get settings(): Observable<PictureSettings> {
    return this.settings$.asObservable();
  }

  get currentSettings(): PictureSettings {
    return this.settings$.value;
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

  private isValidMode(value: string | undefined): value is PictureMode {
    return value === 'mix' || value === 'flicker' || value === 'interlace1' || value === 'interlace2';
  }
}
