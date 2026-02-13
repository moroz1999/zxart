import {CommonModule} from '@angular/common';
import {BrowserModule} from '@angular/platform-browser';
import {DoBootstrap, Injector, NgModule, Type} from '@angular/core';
import {ZxProdsCategoryComponent} from './entities/zx-prods-category/zx-prods-category.component';
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
import {MatBottomSheetModule} from '@angular/material/bottom-sheet';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {ZxProdsListComponent} from './entities/zx-prods-list/zx-prods-list.component';
import {AppComponent} from './app.component';
import {ParserComponent} from './features/parser/parser.component';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {environment} from '../environments/environment';
import {CommentsListComponent} from './features/comments/components/comments-list/comments-list.component';
import {CommentsPageComponent} from './features/comments/components/comments-page/comments-page.component';
import {SettingsTriggerComponent} from './features/settings/components/settings-trigger/settings-trigger.component';
import {LatestCommentsComponent} from './features/comments/components/latest-comments/latest-comments.component';
import {
  RecentRatingsWidgetComponent
} from './features/ratings/components/recent-ratings-widget/recent-ratings-widget.component';
import {RatingsListComponent} from './features/ratings/components/ratings-list/ratings-list.component';
import {FirstpageComponent} from './features/firstpage/components/firstpage/firstpage.component';
import {PlayerHostComponent} from './features/player/components/player-host/player-host.component';
import {LegacyPlayButtonComponent} from './features/player/components/legacy-play-button/legacy-play-button.component';
import {RadioRemoteComponent} from './features/radio-remote/components/radio-remote/radio-remote.component';
import {AuthorTunesComponent} from './features/author-tunes/components/author-tunes/author-tunes.component';

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
        MatBottomSheetModule,
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
            'zx-prods-category': ZxProdsCategoryComponent,
            'zx-prods-list': ZxProdsListComponent,
            'zx-parser': ParserComponent,
            'zx-comments-list': CommentsListComponent,
            'zx-comments-page': CommentsPageComponent,
            'zx-settings-trigger': SettingsTriggerComponent,
            'zx-latest-comments': LatestCommentsComponent,
            'zx-recent-ratings': RecentRatingsWidgetComponent,
            'zx-ratings-list': RatingsListComponent,
            'zx-firstpage': FirstpageComponent,
            'zx-player': PlayerHostComponent,
            'zx-legacy-play': LegacyPlayButtonComponent,
            'zx-radio-remote': RadioRemoteComponent,
            'zx-author-tunes': AuthorTunesComponent,
        } as { [key: string]: Type<Object> };
        for (const selector of Object.keys(elements)) {
            const element = createCustomElement(elements[selector], {injector: this.injector});
            customElements.define(selector, element);
        }
    }
}
