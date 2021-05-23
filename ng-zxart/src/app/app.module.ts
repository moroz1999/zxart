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
import {ZxProdBlockComponent} from './zx-prod-block/zx-prod-block.component';
import {FormsModule} from '@angular/forms';
import {DialogSelectorComponent} from './zx-prods-list/components/dialog-selector/dialog-selector.component';
import {MatDialogModule} from '@angular/material/dialog';
import {MatButtonModule} from '@angular/material/button';
import {MatCheckboxModule} from '@angular/material/checkbox';
import {MatChipsModule} from '@angular/material/chips';
import {MatIconModule} from '@angular/material/icon';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {MatTreeModule} from '@angular/material/tree';
import {MatCardModule} from '@angular/material/card';
import {MatButtonToggleModule} from '@angular/material/button-toggle';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {DialogSelectorDialogComponent} from './zx-prods-list/components/dialog-selector/dialog-selector-dialog/dialog-selector-dialog.component';
import {LetterSelectorComponent} from './zx-prods-list/components/letter-selector/letter-selector.component';
import {SortingSelectorComponent} from './zx-prods-list/components/sorting-selector/sorting-selector.component';
import {TagsSelectorComponent} from './shared/components/tags-selector/tags-selector.component';
import {CategoriesTreeSelectorComponent} from './zx-prods-list/components/categories-tree-selector/categories-tree-selector.component';
import {ZxProdRowComponent} from './zx-prod-row/zx-prod-row.component';

export function HttpLoaderFactory(httpClient: HttpClient): TranslateHttpLoader {
  return new TranslateHttpLoader(httpClient, `${environment.assetsUrl}i18n/`);
}

@NgModule({
  declarations: [
    ZxProdsListComponent,
    PagesSelectorComponent,
    ZxProdBlockComponent,
    DialogSelectorComponent,
    DialogSelectorDialogComponent,
    LetterSelectorComponent,
    SortingSelectorComponent,
    TagsSelectorComponent,
    CategoriesTreeSelectorComponent,
    ZxProdRowComponent,
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
    FormsModule,
    BrowserAnimationsModule,
    MatDialogModule,
    MatButtonModule,
    MatCheckboxModule,
    MatChipsModule,
    MatIconModule,
    MatAutocompleteModule,
    MatTreeModule,
    MatCardModule,
    MatButtonToggleModule,
  ],
  providers: [],
  bootstrap: [],
  entryComponents: [ZxProdsListComponent],
})
export class AppModule {

  constructor(private injector: Injector) {
    const el = createCustomElement(ZxProdsListComponent, {injector: this.injector});
    customElements.define('app-zx-prods-list', el);
  }

  ngDoBootstrap(): void {
  }
}
