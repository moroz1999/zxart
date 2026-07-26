import {HttpClient, HttpParams} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {TagPageDto, TagPageSection} from '../models/tag-page.dto';

@Injectable({providedIn: 'root'})
export class TagPageApiService {
  constructor(private readonly http: HttpClient) {}

  get(tagId: number, section: TagPageSection): Observable<TagPageDto | null> {
    const params = new HttpParams()
      .set('id', String(tagId))
      .set('section', section);

    return this.http.get<TagPageDto>('/tag-details/', {params}).pipe(
      catchError(() => of(null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }
}
