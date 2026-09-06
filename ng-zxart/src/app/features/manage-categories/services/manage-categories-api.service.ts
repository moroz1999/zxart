import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {BehaviorSubject, defer, Observable} from 'rxjs';
import {filter, tap} from 'rxjs/operators';
import {CategorySaveRequest, ManageCategoriesDto} from '../models/manage-category.dto';

const EMPTY_TREE: ManageCategoriesDto = {languages: [], categories: []};

/**
 * The editable category tree behind `/categories-data/`.
 *
 * Every write answers with the refreshed tree, so the store is replaced from
 * the response instead of being patched — the server owns the order and the
 * production counts. Errors are deliberately **not** swallowed here: the
 * management screen has to show why a save or a delete was refused (a category
 * that still holds productions, a title missing in one language), so callers
 * subscribe with an error handler.
 */
@Injectable({
  providedIn: 'root',
})
export class ManageCategoriesApiService {
  private readonly apiUrl = '/categories-data/';
  private readonly store = new BehaviorSubject<ManageCategoriesDto | null>(null);
  private loading = false;

  readonly tree$: Observable<ManageCategoriesDto> = defer(() => {
    if (this.store.getValue() === null && !this.loading) {
      this.load();
    }
    return this.store.pipe(filter((tree): tree is ManageCategoriesDto => tree !== null));
  });

  constructor(private readonly http: HttpClient) {}

  create(request: CategorySaveRequest): Observable<ManageCategoriesDto> {
    return this.post('create', request);
  }

  update(request: CategorySaveRequest): Observable<ManageCategoriesDto> {
    return this.post('update', request);
  }

  delete(id: number): Observable<ManageCategoriesDto> {
    return this.post('delete', {id});
  }

  private post(action: string, body: unknown): Observable<ManageCategoriesDto> {
    return this.http
      .post<ManageCategoriesDto>(`${this.apiUrl}?action=${action}`, body)
      .pipe(tap(tree => this.store.next(tree)));
  }

  private load(): void {
    this.loading = true;
    this.http.get<ManageCategoriesDto>(this.apiUrl).subscribe({
      next: tree => {
        this.loading = false;
        this.store.next(tree);
      },
      error: () => {
        this.loading = false;
        this.store.next(EMPTY_TREE);
      },
    });
  }
}
