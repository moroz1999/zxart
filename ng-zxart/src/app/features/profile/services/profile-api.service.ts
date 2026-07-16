import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {UserProfileDto} from '../models/user-profile.dto';

@Injectable({providedIn: 'root'})
export class ProfileApiService {
  private readonly apiUrl = '/profile-data/';

  constructor(private readonly http: HttpClient) {}

  /** Current user's editable profile, or null when not authenticated (401). */
  getProfile(): Observable<UserProfileDto | null> {
    return this.http.get<UserProfileDto>(this.apiUrl).pipe(catchError(() => of(null)));
  }

  /** Persists the profile and returns the saved values. */
  saveProfile(profile: Partial<UserProfileDto>): Observable<UserProfileDto> {
    return this.http.post<UserProfileDto>(`${this.apiUrl}?action=save`, profile);
  }
}
