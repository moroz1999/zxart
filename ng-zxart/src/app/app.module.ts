import {CommonModule} from '@angular/common';
import {BrowserModule} from '@angular/platform-browser';
import {Injector, NgModule} from '@angular/core';
import {ZxProdsListComponent} from './zx-prods-list/zx-prods-list.component';
import {createCustomElement} from '@angular/elements';
import {AngularSvgIconModule} from 'angular-svg-icon';
import {TranslateLoader, TranslateModule} from '@ngx-translate/core';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {HttpClient, HttpClientModule} from '@angular/common/http';
import {PagesSelectorComponent} from './shared/components/pages-selector/pages-selector.component';
import {environment} from '../environments/environment';
import { ZxProdComponent } from './zx-prod/zx-prod.component';

export function HttpLoaderFactory(httpClient: HttpClient): TranslateHttpLoader {
  return new TranslateHttpLoader(httpClient, `${environment.assetsUrl}i18n/`);
}

@NgModule({
  declarations: [
    ZxProdsListComponent,
    PagesSelectorComponent,
    ZxProdComponent,
  ],
  imports: [
    CommonModule,
    BrowserModule,
    HttpClientModule,
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: HttpLoaderFactory,
        deps: [HttpClient],
      },
    }),
    AngularSvgIconModule.forRoot(),
  ],
  providers: [],
  bootstrap: [],
  entryComponents: [ZxProdsListComponent]
})
export class AppModule {

  constructor(private injector: Injector) {
    const el = createCustomElement(ZxProdsListComponent, {injector: this.injector});
    customElements.define('app-zx-prods-list', el);
  }

  ngDoBootstrap(): void {
  }
}
