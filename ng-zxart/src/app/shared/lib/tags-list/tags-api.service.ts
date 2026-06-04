import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {TagItem} from '../../models/tag-item';
import {TagsPayloadDto} from './tags-payload.dto';

@Injectable({
  providedIn: 'root',
})
export class TagsApiService {
  private readonly apiUrl = '/tags/';

  constructor(private readonly http: HttpClient) {}

  getTags(elementId: number): Observable<TagsPayloadDto> {
    return this.http.get<TagsPayloadDto>(this.apiUrl, {
      params: {
        id: elementId,
      },
    });
  }

  saveTags(elementId: number, tags: TagItem[]): Observable<TagsPayloadDto> {
    return this.http.post<TagsPayloadDto>(this.apiUrl, {tags: tags.map(tag => tag.title)}, {
      params: {
        id: elementId,
      },
    });
  }
}
