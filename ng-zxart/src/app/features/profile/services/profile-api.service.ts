import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {PasswordChangeRequest, UserProfileDto} from '../models/user-profile.dto';

@Injectable({providedIn: 'root'})
export class ProfileApiService {
  private readonly apiUrl = '/profile-data/';

  constructor(private readonly http: HttpClient) {}

  /** Current user's account, or null when not authenticated (401). */
  getProfile(): Observable<UserProfileDto | null> {
    return this.http.get<UserProfileDto>(this.apiUrl).pipe(catchError(() => of(null)));
  }

  /** Replaces the account password; the backend rejects a wrong current password. */
  changePassword(request: PasswordChangeRequest): Observable<UserProfileDto | null> {
    return this.http.post<UserProfileDto>(`${this.apiUrl}?action=change-password`, request).pipe(
      catchError(() => of(null)),
    );
  }
}
