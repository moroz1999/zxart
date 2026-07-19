import {ApplicationConfig, importProvidersFrom} from '@angular/core';
import {
  provideRouter,
  withComponentInputBinding,
  withDisabledInitialNavigation,
} from '@angular/router';
import {provideHttpClient, withInterceptors, HttpClient} from '@angular/common/http';
import {provideAnimations} from '@angular/platform-browser/animations';
import {provideTranslateService, TranslateLoader} from '@ngx-translate/core';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {AngularSvgIconModule} from 'angular-svg-icon';
import {APP_ROUTES} from './app.routes';
import {languageInterceptor} from './features/settings/interceptors/language.interceptor';
import {environment} from '../environments/environment';

export function HttpLoaderFactory(http: HttpClient): TranslateHttpLoader {
  return new TranslateHttpLoader(http, `${environment.assetsUrl}i18n/`, '.json');
}

/**
 * Root application configuration for the standalone SPA. Initial navigation is
 * disabled here and started manually in SpaRootComponent, so the interface
 * language is set up before the first navigation resolves.
 */
export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(APP_ROUTES, withDisabledInitialNavigation(), withComponentInputBinding()),
    provideHttpClient(withInterceptors([languageInterceptor])),
    provideAnimations(),
    provideTranslateService({
      loader: {
        provide: TranslateLoader,
        useFactory: HttpLoaderFactory,
        deps: [HttpClient],
      },
    }),
    importProvidersFrom(AngularSvgIconModule.forRoot()),
  ],
};
