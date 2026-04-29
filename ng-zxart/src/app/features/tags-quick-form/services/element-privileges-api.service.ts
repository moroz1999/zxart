import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class ElementPrivilegesApiService {
  private readonly apiUrl = '/element-privileges/';

  constructor(private readonly http: HttpClient) {}

  getPrivileges(elementId: number, privilegeNames: string[]): Observable<Record<string, boolean>> {
    return this.http.get<Record<string, boolean>>(this.apiUrl, {
      params: {
        id: elementId,
        privileges: privilegeNames.join(','),
      },
    });
  }
}
