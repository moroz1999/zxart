import {Injectable} from '@angular/core';
import {map, Observable, of, switchMap, take} from 'rxjs';
import {EMPTY_RADIO_CRITERIA, RadioCriteria} from '../models/radio-criteria';
import {UserPreferencesService} from '../../settings/services/user-preferences.service';
import {CurrentUserService} from '../../../shared/services/current-user.service';
import {LocalStorageService} from '../../../shared/services/local-storage.service';

const STORAGE_KEY = 'radio-criteria';
const PREF_CODE = 'radio_criteria';

@Injectable({
  providedIn: 'root'
})
export class RadioCriteriaStorageService {
  constructor(
    private userPreferencesService: UserPreferencesService,
    private currentUserService: CurrentUserService,
    private localStorage: LocalStorageService,
  ) {}

  loadCriteria(): Observable<RadioCriteria> {
    return this.currentUserService.user$.pipe(take(1),
      switchMap((user) => {
        if (user && user.userName !== 'anonymous') {
          return this.userPreferencesService.syncFromServer().pipe(
            map(() => this.parseCriteria(this.userPreferencesService.getPreference(PREF_CODE))),
          );
        }
        return of(this.readFromStorage());
      }),
    );
  }

  saveCriteria(criteria: RadioCriteria): Observable<void> {
    return this.currentUserService.user$.pipe(take(1),
      switchMap((user) => {
        if (user && user.userName !== 'anonymous') {
          return this.userPreferencesService.setPreference(PREF_CODE, JSON.stringify(criteria)).pipe(
            map(() => undefined),
          );
        }
        this.localStorage.set(STORAGE_KEY, criteria);
        return of(undefined);
      }),
    );
  }

  private readFromStorage(): RadioCriteria {
    const data = this.localStorage.get<Partial<RadioCriteria>>(STORAGE_KEY);
    if (!data) {
      return EMPTY_RADIO_CRITERIA;
    }
    return {...EMPTY_RADIO_CRITERIA, ...data};
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
