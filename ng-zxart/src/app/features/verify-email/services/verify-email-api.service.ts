import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {VerifyEmailResult} from '../models/verify-email-result';

@Injectable({providedIn: 'root'})
export class VerifyEmailApiService {
  constructor(private readonly http: HttpClient) {}

  verify(email: string, key: string): Observable<VerifyEmailResult> {
    return this.http.post<VerifyEmailResult>('/verify-email-data/', {email, key}).pipe(
      catchError(error => of({
        success: false,
        message: error?.error?.errorMessage ?? 'verify-email.error-generic',
      })),
    );
  }
}
