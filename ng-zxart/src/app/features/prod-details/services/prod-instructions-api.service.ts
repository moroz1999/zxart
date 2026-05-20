import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdInstructionFileDto, ProdInstructionsPayload} from '../models/prod-instruction-file.dto';

@Injectable({providedIn: 'root'})
export class ProdInstructionsApiService {
  constructor(private readonly http: HttpClient) {}

  getInstructions(elementId: number): Observable<ProdInstructionFileDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdInstructionsPayload>('/prod-instructions/', {params}).pipe(
      map(response => response.files ?? []),
      catchError(() => of([])),
    );
  }
}
