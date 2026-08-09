import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {BehaviorSubject, defer, Observable} from 'rxjs';
import {filter, map, tap} from 'rxjs/operators';
import {HardwareCatalogDto, HardwareItemDto, HardwareSaveRequest} from '../models/hardware-catalog.dto';

const EMPTY_CATALOG: HardwareCatalogDto = {languages: [], categories: [], items: []};

/**
 * The editable hardware catalog behind `/hardware-data/`.
 *
 * Every write answers with the refreshed catalog, so the store is replaced from
 * the response instead of being patched — the server owns ordering and usage
 * counts. Errors are deliberately **not** swallowed here: the management form
 * has to show why a save was refused (duplicate code, item still in use), so
 * callers subscribe with an error handler.
 */
@Injectable({
  providedIn: 'root',
})
export class ManageHardwareApiService {
  private readonly apiUrl = '/hardware-data/';
  private readonly store = new BehaviorSubject<HardwareCatalogDto | null>(null);
  private loading = false;

  readonly catalog$: Observable<HardwareCatalogDto> = defer(() => {
    if (this.store.getValue() === null && !this.loading) {
      this.load();
    }
    return this.store.pipe(filter((catalog): catalog is HardwareCatalogDto => catalog !== null));
  });

  readonly items$: Observable<HardwareItemDto[]> = this.catalog$.pipe(map(catalog => catalog.items));

  constructor(private readonly http: HttpClient) {}

  itemById$(id: number): Observable<HardwareItemDto | null> {
    return this.items$.pipe(map(items => items.find(item => item.id === id) ?? null));
  }

  create(request: HardwareSaveRequest): Observable<HardwareCatalogDto> {
    return this.post('create', request);
  }

  update(request: HardwareSaveRequest): Observable<HardwareCatalogDto> {
    return this.post('update', request);
  }

  delete(id: number): Observable<HardwareCatalogDto> {
    return this.post('delete', {id});
  }

  private post(action: string, body: unknown): Observable<HardwareCatalogDto> {
    return this.http
      .post<HardwareCatalogDto>(`${this.apiUrl}?action=${action}`, body)
      .pipe(tap(catalog => this.store.next(catalog)));
  }

  private load(): void {
    this.loading = true;
    this.http.get<HardwareCatalogDto>(this.apiUrl).subscribe({
      next: catalog => {
        this.loading = false;
        this.store.next(catalog);
      },
      error: () => {
        this.loading = false;
        this.store.next(EMPTY_CATALOG);
      },
    });
  }
}
