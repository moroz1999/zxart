import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {EMPTY, Observable} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {JsonResponse} from '../models/json-response';
import {StructureElement} from '../models/structure-element';
import {ElementResponseData} from '../models/element-response-data';
import {environment} from '../../../environments/environment';

export interface PostParameters {
    [key: string]: string | number | boolean;
}

@Injectable({
    providedIn: 'root',
})
export class ElementsService {
    private apiUrl: string = `//${environment.apiBaseUrl}jsonElementData/`;

    constructor(private http: HttpClient) {
    }

    getModel<T, U extends StructureElement>(elementId: number, className: {
        new(dto: T): U
    }, postParameters: PostParameters, preset: string, structureType = ''): Observable<U> {
        // When there is no element id the backend resolves the collection root by
        // structure type (SPA collection pages carry no hardcoded wrapper id).
        const allParameters: PostParameters = {
            ...postParameters,
            preset,
            ...(elementId > 0 ? {elementId} : structureType ? {structureType} : {elementId}),
        };
        const options: Object = {
            'params': allParameters,
        };
        return this.http
            .get<JsonResponse<ElementResponseData<T>>>(this.apiUrl, options)
            .pipe(
                map(response => {
                    return new className(response.responseData.elementData);
                }),
                catchError(() => EMPTY),
            );
    }
}
