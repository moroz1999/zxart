import {CommonModule} from '@angular/common';
import {BrowserModule} from '@angular/platform-browser';
import {Injector, NgModule} from '@angular/core';
// import { AppRoutingModule } from './app-routing.module';
// import {AppComponent} from './app.component';
import {ZxProdsListComponent} from './zx-prods-list/zx-prods-list.component';
import {createCustomElement} from '@angular/elements';
import {AngularSvgIconModule} from 'angular-svg-icon';
// import {TranslateLoader, TranslateModule} from '@ngx-translate/core';
// import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {HttpClient, HttpClientModule} from '@angular/common/http';

// export function HttpLoaderFactory(httpClient: HttpClient) {
//   return new TranslateHttpLoader(httpClient, '/public/assets/i18n/');
// }

@NgModule({
  declarations: [
    // AppComponent,
    ZxProdsListComponent
  ],
  imports: [
    CommonModule,
    BrowserModule,
    HttpClientModule,
    // TranslateModule.forRoot({
    //   loader: {
    //     provide: TranslateLoader,
    //     useFactory: HttpLoaderFactory,
    //     deps: [HttpClient]
    //   }
    // }),
    AngularSvgIconModule.forRoot(),
    // AppRoutingModule
  ],
  providers: [],
  // bootstrap: [AppComponent],
  bootstrap: [],
  entryComponents: [ZxProdsListComponent]
})
export class AppModule {

  constructor(private injector: Injector) {
    const el = createCustomElement(ZxProdsListComponent, {injector: this.injector});
    customElements.define('app-zx-prods-list', el);
  }

  ngDoBootstrap() {
  }
}