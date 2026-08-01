import {Injectable} from '@angular/core';
import {map, Observable} from 'rxjs';
import {EMPTY_RADIO_CRITERIA, RadioCriteria} from '../models/radio-criteria';
import {UserPreferencesService} from '../../settings/services/user-preferences.service';

const PREF_CODE = 'radio_criteria';

@Injectable({
  providedIn: 'root'
})
export class RadioCriteriaStorageService {
  constructor(private userPreferencesService: UserPreferencesService) {}

  loadCriteria(): Observable<RadioCriteria> {
    return this.userPreferencesService.initialize().pipe(
      map((preferences) => this.parseCriteria(preferences[PREF_CODE])),
    );
  }

  saveCriteria(criteria: RadioCriteria): Observable<void> {
    return this.userPreferencesService.setPreference(PREF_CODE, JSON.stringify(criteria)).pipe(
      map(() => undefined),
    );
  }

  private parseCriteria(raw: string | undefined | null): RadioCriteria {
    if (!raw) {
      return EMPTY_RADIO_CRITERIA;
    }
    try {
      const data = JSON.parse(raw);
      return {...EMPTY_RADIO_CRITERIA, ...data};
    } catch {
      return EMPTY_RADIO_CRITERIA;
    }
  }
}
