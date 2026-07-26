import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {PasswordReminderResult} from '../models/password-reminder-result';

@Injectable({providedIn: 'root'})
export class PasswordReminderApiService {
  constructor(private readonly http: HttpClient) {}

  request(email: string): Observable<PasswordReminderResult> {
    return this.send({action: 'request', email});
  }

  reset(email: string, key: string, password: string, passwordRepeat: string): Observable<PasswordReminderResult> {
    return this.send({
      action: 'reset', email, key, password, passwordRepeat,
    });
  }

  private send(body: Record<string, string>): Observable<PasswordReminderResult> {
    return this.http.post<PasswordReminderResult>('/password-reminder-data/', body).pipe(
      catchError(error => of({
        success: false,
        message: error?.error?.errorMessage ?? 'password-reminder.error-generic',
      })),
    );
  }
}
