import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {environment} from '../../../../environments/environment';
import {LanguageItem} from '../models/language-item';

@Injectable({
  providedIn: 'root',
})
export class LanguagesService {
  private readonly apiUrl = `${environment.apiBaseUrl}languages/`;

  constructor(private http: HttpClient) {}

  getLanguages(path: string): Observable<LanguageItem[]> {
    return this.http.get<LanguageItem[]>(this.apiUrl, {params: {path}});
  }
}
