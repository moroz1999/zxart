import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {BehaviorSubject, Observable} from 'rxjs';
import {map, take} from 'rxjs/operators';
import {JsonResponse} from '../models/json-response';
import {StructureElement} from '../models/structure-element';
import {ElementResponseData} from '../models/element-response-data';
import {environment} from '../../../environments/environment';

export interface PostParameters {
  [key: string]: Primitive;
}

declare var elementsData: { [key: number]: any };

@Injectable({
  providedIn: 'root',
})
export class ElementsService {
  private apiUrl: string = `//${environment.apiBaseUrl}jsonElementData/`;

  constructor(private http: HttpClient) {
  }

  getModel<T, U extends StructureElement>(elementId: number, className: { new(dto: T): U }, postParameters: PostParameters, preset: string): Observable<U> {
    if (elementsData && elementsData[elementId]) {
      const model = new className(elementsData[elementId] as T);
      setTimeout(() => delete elementsData[elementId], 1000);
      return new BehaviorSubject<U>(model).pipe(take(1));
    } else {
      postParameters['elementId'] = elementId;
      postParameters['preset'] = preset;
      const options: Object = {
        'params': postParameters,
      };
      return this.http
        .get<JsonResponse<ElementResponseData<T>>>(this.apiUrl, options)
        .pipe(
          map(response => {
            return new className(response.responseData.elementData);
          }),
        );
    }
  }
}
