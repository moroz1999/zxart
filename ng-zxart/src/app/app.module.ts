import {CommonModule} from '@angular/common';
import {BrowserModule} from '@angular/platform-browser';
import {DoBootstrap, Injector, NgModule, Type} from '@angular/core';
import {ZxProdsCategoryComponent} from './zx-prods-category/zx-prods-category.component';
import {createCustomElement} from '@angular/elements';
import {AngularSvgIconModule} from 'angular-svg-icon';
import {provideTranslateService, TranslateLoader, TranslatePipe} from '@ngx-translate/core';
import {HttpClient, HttpClientModule, provideHttpClient} from '@angular/common/http';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
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
import {ZxProdsListComponent} from './zx-prods-list/zx-prods-list.component';
import {AppComponent} from './app.component';
import {ParserComponent} from './parser/parser.component';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {environment} from '../environments/environment';
import {CommentsListComponent} from './features/comments/components/comments-list/comments-list.component';
import {SettingsTriggerComponent} from './features/settings/components/settings-trigger/settings-trigger.component';

export function HttpLoaderFactory(http: HttpClient) {
    return new TranslateHttpLoader(http, `${environment.assetsUrl}i18n/`, '.json');
}

@NgModule({
    declarations: [
    ],
    imports: [
        CommonModule,
        BrowserModule,
        HttpClientModule,
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
        TranslatePipe,
    ],
    providers: [
        provideHttpClient(),
        provideTranslateService({
            loader: {
                provide: TranslateLoader,
                useFactory: HttpLoaderFactory,
                deps: [HttpClient],
            },
            defaultLanguage: 'en',
        }),
    ],
    bootstrap: [],
})
export class AppModule implements DoBootstrap  {
    constructor(private injector: Injector) {
    }

    public ngDoBootstrap(): void {
        const elements = {
            'app-root': AppComponent,
            'app-zx-prods-category': ZxProdsCategoryComponent,
            'app-zx-prods-list': ZxProdsListComponent,
            'app-parser': ParserComponent,
            'app-comments-list': CommentsListComponent,
            'app-settings-trigger': SettingsTriggerComponent,
        } as { [key: string]: Type<Object> };
        for (const selector of Object.keys(elements)) {
            const element = createCustomElement(elements[selector], {injector: this.injector});
            customElements.define(selector, element);
        }
    }
}
