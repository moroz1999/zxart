import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {JsonResponse} from '../models/json-response';
import {StructureElement} from '../models/structure-element';

export interface PostParameters {
  [key: string]: Primitive;
}

@Injectable({
  providedIn: 'root',
})
export class ElementsService {
  private apiUrl: string = `//${location.hostname}/jsonElementData/`;

  constructor(private http: HttpClient) {
  }

  getModel<T, U extends StructureElement>(elementId: number, c: { new(dto: T): U }, parameters: PostParameters): Observable<U> {
    parameters.elementId = elementId;
    const options: Object = {
      'params': parameters,
    };
    return this.http
      .get<JsonResponse<T>>(this.apiUrl, options)
      .pipe(
        map(response => {
            return new c(response.responseData);
          },
        ),
      );
  }
}
