import {BehaviorSubject, firstValueFrom, of} from 'rxjs';
import {PreferenceDto, PreferenceValues} from '../../settings/models/preference.dto';
import {UserPreferencesService} from '../../settings/services/user-preferences.service';
import {FirstpageConfigService} from './firstpage-config.service';

describe('FirstpageConfigService', () => {
  it('keeps the rolling start-year offset when loading and saving the homepage config', async () => {
    const preferences = new BehaviorSubject<PreferenceValues>({
      homepage_order: 'newProds',
      homepage_new_prods_limit: '20',
      homepage_new_prods_min_rating: '0',
      homepage_new_prods_start_year: '1',
    });
    const setPreferences = jasmine.createSpy('setPreferences').and.callFake(
      (items: PreferenceDto[]) => {
        const values = Object.fromEntries(items.map(item => [item.code, item.value]));
        preferences.next(values);
        return of(values);
      },
    );
    const preferencesService = {
      preferences$: preferences.asObservable(),
      setPreferences,
    } as unknown as UserPreferencesService;
    const service = new FirstpageConfigService(preferencesService);

    const config = await firstValueFrom(service.getCurrentConfig());
    const newProds = config.modules.find(module => module.type === 'newProds');

    expect(newProds?.settings.startYearOffset).toBe(1);
    expect(newProds?.settings.minRating).toBe(0);

    await firstValueFrom(service.saveConfig(config.modules));

    const savedItems = setPreferences.calls.mostRecent().args[0] as PreferenceDto[];
    expect(savedItems).toContain({
      code: 'homepage_new_prods_start_year',
      value: '1',
    });
    expect(savedItems).toContain({
      code: 'homepage_new_prods_min_rating',
      value: '0',
    });
    const savedConfig = await firstValueFrom(service.getCurrentConfig());
    expect(savedConfig.modules.find(module => module.type === 'newProds')?.settings.startYearOffset).toBe(1);
  });
});
