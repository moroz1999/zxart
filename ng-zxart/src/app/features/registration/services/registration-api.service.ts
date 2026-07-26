import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {RegistrationPayload, RegistrationResult} from '../models/registration';

@Injectable({providedIn: 'root'})
export class RegistrationApiService {
  constructor(private readonly http: HttpClient) {}

  register(payload: RegistrationPayload): Observable<RegistrationResult> {
    return this.http.post<RegistrationResult>('/register-data/', payload).pipe(
      catchError(error => of({
        success: false,
        message: error?.error?.errorMessage ?? 'register.error-generic',
      })),
    );
  }
}
