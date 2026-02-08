import {Injectable} from '@angular/core';
import {environment} from '../../../environments/environment';
import {HttpClient} from '@angular/common/http';
import {map} from 'rxjs/operators';
import {Observable} from 'rxjs';
import {JsonResponse} from '../models/json-response';
import {ParserData} from '../../features/parser/models/parser-data';

@Injectable({
    providedIn: 'root',
})
export class ParserService {
    private apiUrl: string = `//${environment.apiBaseUrl}parser/`;

    constructor(
        private http: HttpClient,
    ) {
    }

    public parseData(file: File): Observable<ParserData[]> {
        let formData = new FormData();
        formData.append('file', file);

        return this.http.post<JsonResponse<ParserData[]>>(this.apiUrl, formData).pipe(
            map(
                response => {
                    if (response.responseStatus === 'success') {
                        return response.responseData;
                    }
                    return [] as ParserData[];
                },
            ),
        );
    }
}
