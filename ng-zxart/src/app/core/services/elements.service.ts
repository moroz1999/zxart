import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {map, catchError} from 'rxjs/operators';
import {JsonResponse} from '../../json-response';
import {ZxProdsList} from '../../models/zx-prods-list.model';

@Injectable({
  providedIn: 'root'
})
export class ElementsService {
  private apiUrl: string = `//${location.hostname}/jsonElementData/`;

  constructor(private http: HttpClient) {
  }

  getModel(elementId: number): Observable<ZxProdsList> {
    const options: Object = {
      'params': {
        'elementId': elementId,
      }
    };
    return this.http
      .get<JsonResponse>(this.apiUrl, options)
      .pipe(
        map(response => {
          const data:any = response.responseData.zxProdCategory;
          return new ZxProdsList(data.id, data.title);
        }
        ),
      );
  }
}
