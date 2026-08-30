import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {ClaimApproval} from '../models/claim-approval';

@Injectable({providedIn: 'root'})
export class ClaimApprovalApiService {
  private readonly apiUrl = '/approve-claim-data/';

  constructor(private readonly http: HttpClient) {}

  getClaim(authorId: number, userId: number): Observable<ClaimApproval> {
    return this.http.get<ClaimApproval>(this.apiUrl, {
      params: {authorId, userId},
    });
  }

  approve(authorId: number, userId: number): Observable<ClaimApproval> {
    return this.http.post<ClaimApproval>(this.apiUrl, null, {
      params: {authorId, userId},
    });
  }
}
