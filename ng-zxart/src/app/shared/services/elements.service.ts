import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {BehaviorSubject, Observable} from 'rxjs';
import {map, take} from 'rxjs/operators';
import {JsonResponse} from '../models/json-response';
import {StructureElement} from '../models/structure-element';
import {ElementResponseData} from '../models/element-response-data';

export interface PostParameters {
  [key: string]: Primitive;
}

declare var elementsData: { [key: number]: any };

@Injectable({
  providedIn: 'root',
})
export class ElementsService {
  private apiUrl: string = `//${location.hostname}/jsonElementData/`;

  constructor(private http: HttpClient) {
  }

  getModel<T, U extends StructureElement>(elementId: number, elementType: string, className: { new(dto: T): U }, parameters: PostParameters): Observable<U> {
    if (elementsData && elementsData[elementId]) {
      const model = new className(elementsData[elementId] as T);
      delete elementsData[elementId];
      return new BehaviorSubject<U>(model).pipe(take(1));
    } else {
      parameters.elementId = elementId;
      const options: Object = {
        'params': parameters,
      };
      return this.http
        .get<JsonResponse<ElementResponseData<T>>>(this.apiUrl, options)
        .pipe(
          map(response => {
            return new className(response.responseData[elementType]);
          }),
        );
    }
  }
}
