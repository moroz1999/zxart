import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {JsonResponse} from '../models/json-response';
import {map} from 'rxjs/operators';
import {HttpClient} from '@angular/common/http';
import {Tag} from '../models/tag';
import {TagResponseDto} from '../models/tag-response-dto';

@Injectable({
    providedIn: 'root',
})
export class TagsSearchService {
    private apiUrl: string = `//${location.hostname}/ajaxSearch/mode:public/types:tag/`;

    constructor(private http: HttpClient) {
    }

    search(tagText: string): Observable<Array<Tag>> {
        const parameters = {
            query: tagText,
        };
        parameters.query = tagText;
        const options: Object = {
            'params': parameters,
        };
        return this.http
            .get<JsonResponse<TagResponseDto>>(this.apiUrl, options)
            .pipe(
                map(response => {
                        const tags = [] as Array<Tag>;
                        if (response.responseData.tag) {
                            response.responseData.tag.forEach(
                                item => tags.push(new Tag(item)),
                            );
                        }
                        return tags;
                    },
                ),
            );
    }

}
