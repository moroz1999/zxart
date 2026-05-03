import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';

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
    }).pipe(
      catchError(() => of(this.buildDeniedPrivileges(privilegeNames))),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private buildDeniedPrivileges(privilegeNames: string[]): Record<string, boolean> {
    return Object.fromEntries(privilegeNames.map(privilegeName => [privilegeName, false]));
  }
}
