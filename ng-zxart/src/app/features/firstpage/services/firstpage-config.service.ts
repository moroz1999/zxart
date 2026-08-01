import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, map, shareReplay, switchMap, take} from 'rxjs/operators';
import {UserPreferencesService} from '../../settings/services/user-preferences.service';
import {PreferenceValues} from '../../settings/models/preference.dto';
import {
  ALL_MODULE_TYPES,
  FirstpageConfig,
  MODULE_LIMIT_PREF_CODES,
  MODULE_MIN_RATING_PREF_CODES,
  MODULE_START_YEAR_PREF_CODES,
  ModuleConfig,
  ModuleSettings,
  ModuleType,
} from '../models/firstpage-config';

@Injectable({
  providedIn: 'root'
})
export class FirstpageConfigService {
  private readonly config$: Observable<FirstpageConfig>;

  constructor(private preferencesService: UserPreferencesService) {
    this.config$ = this.preferencesService.preferences$.pipe(
      map(preferences => this.buildConfig(preferences)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  getConfig(): Observable<FirstpageConfig> {
    return this.config$;
  }

  getCurrentConfig(): Observable<FirstpageConfig> {
    return this.config$.pipe(take(1));
  }

  saveConfig(modules: ModuleConfig[]): Observable<boolean> {
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

      const startYearCode = MODULE_START_YEAR_PREF_CODES[mod.type];
      if (startYearCode && mod.settings.startYearOffset !== undefined) {
        items.push({code: startYearCode, value: String(mod.settings.startYearOffset)});
      }
    }

    return this.preferencesService.setPreferences(items).pipe(
      map(() => true),
      catchError(() => of(false)),
    );
  }

  resetToDefaults(): Observable<boolean> {
    return this.preferencesService.getDefaults().pipe(
      map(defaults => Object.entries(defaults)
        .filter(([code]) => code.startsWith('homepage_'))
        .map(([code, value]) => ({code, value}))),
      switchMap(items => items.length > 0
        ? this.preferencesService.setPreferences(items).pipe(map(() => true))
        : of(false)),
      catchError(() => of(false)),
    );
  }

  private buildConfig(preferences: PreferenceValues): FirstpageConfig {
    const orderStr = preferences['homepage_order'];
    const disabledStr = preferences['homepage_disabled'];

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
      settings: this.buildModuleSettings(type, preferences),
    }));

    return {modules};
  }

  private buildModuleSettings(type: ModuleType, preferences: PreferenceValues): ModuleSettings {
    const limitCode = MODULE_LIMIT_PREF_CODES[type];
    const limitStr = preferences[limitCode];
    const limit = limitStr ? parseInt(limitStr, 10) || 10 : 10;

    const ratingCode = MODULE_MIN_RATING_PREF_CODES[type];
    let minRating: number | undefined;
    if (ratingCode) {
      const ratingStr = preferences[ratingCode];
      if (ratingStr) {
        minRating = parseFloat(ratingStr);
      }
    }

    const startYearCode = MODULE_START_YEAR_PREF_CODES[type];
    let startYearOffset: number | undefined;
    if (startYearCode) {
      const startYearStr = preferences[startYearCode];
      if (startYearStr) {
        startYearOffset = parseInt(startYearStr, 10) || 0;
      }
    }

    return {limit, ...(minRating !== undefined ? {minRating} : {}), ...(startYearOffset !== undefined ? {startYearOffset} : {})};
  }
}
