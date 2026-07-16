import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';

export interface RegistrationPayload {
  userName: string;
  email: string;
  password: string;
  passwordRepeat: string;
  firstName?: string;
  lastName?: string;
  company?: string;
  address?: string;
  city?: string;
  postIndex?: string;
  country?: string;
  phone?: string;
  website?: string;
}

export interface RegistrationResult {
  success: boolean;
  message: string;
}

@Injectable({providedIn: 'root'})
export class RegistrationApiService {
  constructor(private readonly http: HttpClient) {}

  register(payload: RegistrationPayload): Observable<RegistrationResult> {
    return this.http.post<RegistrationResult>('/register-data/', payload);
  }
}
