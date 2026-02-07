import {Injectable} from '@angular/core';
import {BehaviorSubject, Observable} from 'rxjs';
import {UserPreferencesService} from '../../settings/services/user-preferences.service';
import {
  ALL_MODULE_TYPES,
  DEFAULT_MODULE_SETTINGS,
  FirstpageConfig,
  MODULE_LIMIT_PREF_CODES,
  MODULE_MIN_RATING_PREF_CODES,
  ModuleConfig,
  ModuleType,
} from '../models/firstpage-config';

@Injectable({
  providedIn: 'root'
})
export class FirstpageConfigService {
  private config$ = new BehaviorSubject<FirstpageConfig>(this.buildConfig());

  constructor(private preferencesService: UserPreferencesService) {}

  getConfig(): Observable<FirstpageConfig> {
    return this.config$.asObservable();
  }

  getCurrentConfig(): FirstpageConfig {
    return this.config$.value;
  }

  reload(): void {
    this.config$.next(this.buildConfig());
  }

  saveConfig(modules: ModuleConfig[]): void {
    const items: {code: string; value: string}[] = [];

    const order = modules.map(m => m.type);
    items.push({code: 'homepage_order', value: order.join(',')});

    const disabled = modules.filter(m => !m.enabled).map(m => m.type);
    items.push({code: 'homepage_disabled', value: disabled.join(',')});

    for (const mod of modules) {
      items.push({code: MODULE_LIMIT_PREF_CODES[mod.type], value: String(mod.settings.limit)});

      const ratingCode = MODULE_MIN_RATING_PREF_CODES[mod.type];
      if (ratingCode && mod.settings.minRating !== undefined) {
        items.push({code: ratingCode, value: String(mod.settings.minRating)});
      }
    }

    this.preferencesService.setPreferences(items).subscribe(() => {
      this.reload();
    });
  }

  resetToDefaults(): void {
    const items: {code: string; value: string}[] = [];

    items.push({code: 'homepage_order', value: ALL_MODULE_TYPES.join(',')});
    items.push({code: 'homepage_disabled', value: ''});

    for (const type of ALL_MODULE_TYPES) {
      const defaults = DEFAULT_MODULE_SETTINGS[type];
      items.push({code: MODULE_LIMIT_PREF_CODES[type], value: String(defaults.limit)});

      const ratingCode = MODULE_MIN_RATING_PREF_CODES[type];
      if (ratingCode && defaults.minRating !== undefined) {
        items.push({code: ratingCode, value: String(defaults.minRating)});
      }
    }

    this.preferencesService.setPreferences(items).subscribe(() => {
      this.reload();
    });
  }

  private buildConfig(): FirstpageConfig {
    const orderStr = this.preferencesService.getPreference('homepage_order');
    const disabledStr = this.preferencesService.getPreference('homepage_disabled');

    const order = orderStr
      ? orderStr.split(',').filter((t): t is ModuleType => ALL_MODULE_TYPES.includes(t as ModuleType))
      : [...ALL_MODULE_TYPES];

    const missingTypes = ALL_MODULE_TYPES.filter(t => !order.includes(t));
    const fullOrder = [...order, ...missingTypes];

    const disabledSet = new Set<string>(
      disabledStr ? disabledStr.split(',').filter(Boolean) : []
    );

    const modules: ModuleConfig[] = fullOrder.map((type, index) => ({
      type,
      enabled: !disabledSet.has(type),
      order: index,
      settings: this.buildModuleSettings(type),
    }));

    return {modules};
  }

  private buildModuleSettings(type: ModuleType) {
    const defaults = DEFAULT_MODULE_SETTINGS[type];

    const limitCode = MODULE_LIMIT_PREF_CODES[type];
    const limitStr = this.preferencesService.getPreference(limitCode);
    const limit = limitStr ? parseInt(limitStr, 10) || defaults.limit : defaults.limit;

    const ratingCode = MODULE_MIN_RATING_PREF_CODES[type];
    let minRating = defaults.minRating;
    if (ratingCode) {
      const ratingStr = this.preferencesService.getPreference(ratingCode);
      if (ratingStr) {
        minRating = parseFloat(ratingStr) || defaults.minRating;
      }
    }

    return {limit, ...(minRating !== undefined ? {minRating} : {})};
  }
}
