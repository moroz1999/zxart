import {Injectable} from '@angular/core';
import {merge, Observable, of, Subject} from 'rxjs';
import {map, shareReplay, switchMap, take} from 'rxjs/operators';
import {UserPreferencesService} from '../../settings/services/user-preferences.service';
import {PreferenceDto} from '../../settings/models/preference.dto';
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
  private readonly reload$ = new Subject<void>();

  /**
   * Does NOT emit until preferences are fully loaded from the server.
   * After that, emits immediately and on every reload() call.
   */
  private readonly config$: Observable<FirstpageConfig> = this.preferencesService.initialize().pipe(
    switchMap(() => merge(of(void 0), this.reload$)),
    map(() => this.buildConfig()),
    shareReplay(1),
  );

  constructor(private preferencesService: UserPreferencesService) {}

  getConfig(): Observable<FirstpageConfig> {
    return this.config$;
  }

  getCurrentConfig(): Observable<FirstpageConfig> {
    return this.config$.pipe(take(1));
  }

  reload(): void {
    this.reload$.next();
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

      const startYearCode = MODULE_START_YEAR_PREF_CODES[mod.type];
      if (startYearCode && mod.settings.startYearOffset !== undefined) {
        items.push({code: startYearCode, value: String(mod.settings.startYearOffset)});
      }
    }

    this.preferencesService.setPreferences(items).subscribe(() => {
      this.reload();
    });
  }

  resetToDefaults(): Observable<void> {
    return new Observable(subscriber => {
      this.preferencesService.getDefaults().subscribe(defaults => {
        const items = defaults.filter(d => d.code.startsWith('homepage_'));

        this.preferencesService.setPreferences(items).subscribe(() => {
          this.reload();
          subscriber.next();
          subscriber.complete();
        });
      });
    });
  }

  buildDefaultModules(defaults: PreferenceDto[]): ModuleConfig[] {
    const defaultMap = new Map(defaults.map(d => [d.code, d.value]));

    return ALL_MODULE_TYPES.map((type, index) => ({
      type,
      enabled: true,
      order: index,
      settings: this.buildModuleSettingsFromMap(type, defaultMap),
    }));
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

  private buildModuleSettings(type: ModuleType): ModuleSettings {
    const limitCode = MODULE_LIMIT_PREF_CODES[type];
    const limitStr = this.preferencesService.getPreference(limitCode);
    const limit = limitStr ? parseInt(limitStr, 10) || 10 : 10;

    const ratingCode = MODULE_MIN_RATING_PREF_CODES[type];
    let minRating: number | undefined;
    if (ratingCode) {
      const ratingStr = this.preferencesService.getPreference(ratingCode);
      if (ratingStr) {
        minRating = parseFloat(ratingStr) || undefined;
      }
    }

    const startYearCode = MODULE_START_YEAR_PREF_CODES[type];
    let startYearOffset: number | undefined;
    if (startYearCode) {
      const startYearStr = this.preferencesService.getPreference(startYearCode);
      if (startYearStr) {
        startYearOffset = parseInt(startYearStr, 10) || 0;
      }
    }

    return {limit, ...(minRating !== undefined ? {minRating} : {}), ...(startYearOffset !== undefined ? {startYearOffset} : {})};
  }

  private buildModuleSettingsFromMap(type: ModuleType, prefMap: Map<string, string>): ModuleSettings {
    const limitCode = MODULE_LIMIT_PREF_CODES[type];
    const limitStr = prefMap.get(limitCode);
    const limit = limitStr ? parseInt(limitStr, 10) || 10 : 10;

    const ratingCode = MODULE_MIN_RATING_PREF_CODES[type];
    let minRating: number | undefined;
    if (ratingCode) {
      const ratingStr = prefMap.get(ratingCode);
      if (ratingStr) {
        minRating = parseFloat(ratingStr) || undefined;
      }
    }

    const startYearCode = MODULE_START_YEAR_PREF_CODES[type];
    let startYearOffset: number | undefined;
    if (startYearCode) {
      const startYearStr = prefMap.get(startYearCode);
      if (startYearStr) {
        startYearOffset = parseInt(startYearStr, 10) || 0;
      }
    }

    return {limit, ...(minRating !== undefined ? {minRating} : {}), ...(startYearOffset !== undefined ? {startYearOffset} : {})};
  }
}
