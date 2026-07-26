import {HttpInterceptorFn} from '@angular/common/http';
import {inject} from '@angular/core';
import {LanguageService} from '../services/language.service';

/**
 * Tags every same-origin request with the current interface language
 * (`X-Language`, iso6393). SPA data endpoints read it to return localized
 * content, so the frontend fully owns the language.
 */
export const languageInterceptor: HttpInterceptorFn = (req, next) => {
  if (/^https?:\/\//i.test(req.url)) {
    return next(req);
  }
  const languageService = inject(LanguageService);
  return next(req.clone({setHeaders: {'X-Language': languageService.currentLongCode}}));
};
