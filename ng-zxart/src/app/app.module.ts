import {CommonModule} from '@angular/common';
import {BrowserModule} from '@angular/platform-browser';
import {Injector, NgModule, Type} from '@angular/core';
import {ZxProdsCategoryComponent} from './zx-prods-category/zx-prods-category.component';
import {createCustomElement} from '@angular/elements';
import {AngularSvgIconModule} from 'angular-svg-icon';
import {TranslateLoader, TranslateModule} from '@ngx-translate/core';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {HttpClient, HttpClientModule} from '@angular/common/http';
import {PagesSelectorComponent} from './shared/components/pages-selector/pages-selector.component';
import {environment} from '../environments/environment';
import {ZxProdBlockComponent} from './zx-prod-block/zx-prod-block.component';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {DialogSelectorComponent} from './zx-prods-category/components/dialog-selector/dialog-selector.component';
import {MatDialogModule} from '@angular/material/dialog';
import {MatButtonModule} from '@angular/material/button';
import {MatCheckboxModule} from '@angular/material/checkbox';
import {MatChipsModule} from '@angular/material/chips';
import {MatIconModule} from '@angular/material/icon';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {MatTreeModule} from '@angular/material/tree';
import {MatCardModule} from '@angular/material/card';
import {MatButtonToggleModule} from '@angular/material/button-toggle';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {
    DialogSelectorDialogComponent,
} from './zx-prods-category/components/dialog-selector/dialog-selector-dialog/dialog-selector-dialog.component';
import {LetterSelectorComponent} from './zx-prods-category/components/letter-selector/letter-selector.component';
import {SortingSelectorComponent} from './zx-prods-category/components/sorting-selector/sorting-selector.component';
import {TagsSelectorComponent} from './shared/components/tags-selector/tags-selector.component';
import {
    CategoriesTreeSelectorComponent,
} from './zx-prods-category/components/categories-tree-selector/categories-tree-selector.component';
import {ZxProdRowComponent} from './zx-prod-row/zx-prod-row.component';
import {ZxProdsListComponent} from './zx-prods-list/zx-prods-list.component';
import {AppComponent} from './app.component';
import {RatingComponent} from './shared/components/rating/rating.component';
import {ParserComponent} from './parser/parser.component';
import {ParsedFileComponent} from './parser/parsed-file/parsed-file.component';
import {ParsedReleasesComponent} from './parser/parsed-releases/parsed-releases.component';
import {ParsedReleaseComponent} from './parser/parsed-release/parsed-release.component';

export function HttpLoaderFactory(httpClient: HttpClient): TranslateHttpLoader {
    return new TranslateHttpLoader(httpClient, `${environment.assetsUrl}i18n/`);
}

@NgModule({
    declarations: [
        AppComponent,
        ZxProdsCategoryComponent,
        PagesSelectorComponent,
        ZxProdBlockComponent,
        DialogSelectorComponent,
        DialogSelectorDialogComponent,
        LetterSelectorComponent,
        SortingSelectorComponent,
        TagsSelectorComponent,
        CategoriesTreeSelectorComponent,
        ZxProdRowComponent,
        ZxProdsListComponent,
        RatingComponent,
        ParserComponent,
        ParsedFileComponent,
        ParsedReleasesComponent,
        ParsedReleaseComponent,
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
        MatProgressSpinnerModule,
        ReactiveFormsModule,
    ],
    providers: [],
    bootstrap: [AppComponent, ZxProdsCategoryComponent, ZxProdsListComponent],
})
export class AppModule {
    constructor(private injector: Injector) {
    }

    public ngDoBootstrap(): void {
        const elements = {
            'app-root': AppComponent,
            'app-zx-prods-category': ZxProdsCategoryComponent,
            'app-zx-prods-list': ZxProdsListComponent,
            'app-parser': ParserComponent,
        } as { [key: string]: Type<Object> };
        for (const selector of Object.keys(elements)) {
            const element = createCustomElement(elements[selector], {injector: this.injector});
            customElements.define(selector, element);
        }
    }
}
