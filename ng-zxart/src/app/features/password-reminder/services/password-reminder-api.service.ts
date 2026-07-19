import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';

export interface PasswordReminderResult {
  success: boolean;
  message: string;
}

@Injectable({providedIn: 'root'})
export class PasswordReminderApiService {
  constructor(private readonly http: HttpClient) {}

  request(email: string): Observable<PasswordReminderResult> {
    return this.http.post<PasswordReminderResult>('/password-reminder-data/', {action: 'request', email});
  }

  reset(email: string, key: string, password: string, passwordRepeat: string): Observable<PasswordReminderResult> {
    return this.http.post<PasswordReminderResult>('/password-reminder-data/', {
      action: 'reset', email, key, password, passwordRepeat,
    });
  }
}
