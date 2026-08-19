import {HttpClient, HttpErrorResponse} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {ProdSplitDataDto} from '../models/prod-split-data.dto';

/** Everything of a production the split form offers to move (`/prod-split-data/`). */
@Injectable({providedIn: 'root'})
export class ProdSplitApiService {
  constructor(private readonly http: HttpClient) {}

  getSplitData(elementId: number): Observable<ProdSplitDataDto> {
    return this.http.get<ProdSplitDataDto>('/prod-split-data/', {params: {id: elementId}}).pipe(
      catchError((error: HttpErrorResponse) => of({
        id: 0,
        title: '',
        groups: [],
        errorMessage: error.error?.errorMessage ?? 'split-form.error-load',
      })),
      shareReplay({bufferSize: 1, refCount: true}),
    );
  }
}
