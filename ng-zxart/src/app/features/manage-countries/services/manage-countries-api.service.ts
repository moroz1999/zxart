import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {BehaviorSubject, defer, Observable} from 'rxjs';
import {filter, tap} from 'rxjs/operators';
import {CitySaveRequest, CountrySaveRequest, ManagePlacesDto} from '../models/manage-place.dto';

const EMPTY_PLACES: ManagePlacesDto = {languages: [], countries: [], cities: []};

/**
 * The editable countries and cities behind `/countries-data/`.
 *
 * Countries and cities arrive as two flat lists and every write answers with
 * both of them refreshed, so the store is replaced from the response instead of
 * being patched. Errors are deliberately **not** swallowed here: the management
 * screen has to show why a delete was refused (a country that still has cities,
 * a place an author still names), so callers subscribe with an error handler.
 */
@Injectable({
  providedIn: 'root',
})
export class ManageCountriesApiService {
  private readonly apiUrl = '/countries-data/';
  private readonly store = new BehaviorSubject<ManagePlacesDto | null>(null);
  private loading = false;

  readonly places$: Observable<ManagePlacesDto> = defer(() => {
    if (this.store.getValue() === null && !this.loading) {
      this.load();
    }
    return this.store.pipe(filter((places): places is ManagePlacesDto => places !== null));
  });

  constructor(private readonly http: HttpClient) {}

  createCountry(request: CountrySaveRequest): Observable<ManagePlacesDto> {
    return this.post('createCountry', request);
  }

  updateCountry(request: CountrySaveRequest): Observable<ManagePlacesDto> {
    return this.post('updateCountry', request);
  }

  deleteCountry(id: number): Observable<ManagePlacesDto> {
    return this.post('deleteCountry', {id});
  }

  createCity(request: CitySaveRequest): Observable<ManagePlacesDto> {
    return this.post('createCity', request);
  }

  updateCity(request: CitySaveRequest): Observable<ManagePlacesDto> {
    return this.post('updateCity', request);
  }

  deleteCity(id: number): Observable<ManagePlacesDto> {
    return this.post('deleteCity', {id});
  }

  private post(action: string, body: unknown): Observable<ManagePlacesDto> {
    return this.http
      .post<ManagePlacesDto>(`${this.apiUrl}?action=${action}`, body)
      .pipe(tap(places => this.store.next(places)));
  }

  private load(): void {
    this.loading = true;
    this.http.get<ManagePlacesDto>(this.apiUrl).subscribe({
      next: places => {
        this.loading = false;
        this.store.next(places);
      },
      error: () => {
        this.loading = false;
        this.store.next(EMPTY_PLACES);
      },
    });
  }
}
