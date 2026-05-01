import {CommonModule} from '@angular/common';
import {BrowserModule} from '@angular/platform-browser';
import {DoBootstrap, Injector, NgModule, Type} from '@angular/core';
import {ZxProdsCategoryComponent} from './entities/zx-prods-category/zx-prods-category.component';
import {createCustomElement} from '@angular/elements';
import {AngularSvgIconModule} from 'angular-svg-icon';
import {provideTranslateService, TranslateLoader, TranslatePipe} from '@ngx-translate/core';
import {HttpClient, HttpClientModule, provideHttpClient} from '@angular/common/http';
import {FormsModule} from '@angular/forms';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {ZxProdsListComponent} from './entities/zx-prods-list/zx-prods-list.component';
import {AppComponent} from './app.component';
import {ParserComponent} from './features/parser/parser.component';
import {TranslateHttpLoader} from '@ngx-translate/http-loader';
import {environment} from '../environments/environment';
import {CommentsListComponent} from './features/comments/components/comments-list/comments-list.component';
import {CommentsPageComponent} from './features/comments/components/comments-page/comments-page.component';
import {RatingsListComponent} from './features/ratings/components/ratings-list/ratings-list.component';
import {FirstpageComponent} from './pages/firstpage/firstpage.component';
import {PlayerHostComponent} from './features/player/components/player-host/player-host.component';
import {LegacyPlayButtonComponent} from './features/player/components/legacy-play-button/legacy-play-button.component';
import {AuthorTunesComponent} from './features/author-tunes/components/author-tunes/author-tunes.component';
import {ZxMusicListComponent} from './features/music-list/components/zx-music-list/zx-music-list.component';
import {ZxItemLegacyControlsComponent} from './shared/ui/zx-item-legacy-controls/zx-item-legacy-controls.component';
import {AuthorPicturesComponent} from './features/author-pictures/components/author-pictures/author-pictures.component';
import {ZxPicturesListComponent} from './features/picture-list/components/zx-pictures-list/zx-pictures-list.component';
import {
  ZxPicturesRelatedComponent
} from './features/picture-list/components/zx-pictures-related/zx-pictures-related.component';
import {ZxHeaderComponent} from './features/header/components/zx-header/zx-header.component';
import {ZxRightColumnComponent} from './features/header/components/zx-right-column/zx-right-column.component';
import {
  ZxPictureBrowserComponent
} from './features/picture-browser/components/zx-picture-browser/zx-picture-browser.component';
import {ZxMusicBrowserComponent} from './features/music-browser/components/zx-music-browser/zx-music-browser.component';
import {ZxProdsBrowserComponent} from './features/prods-browser/components/zx-prods-browser/zx-prods-browser.component';
import {
  ZxAuthorBrowserComponent
} from './features/author-browser/components/zx-author-browser/zx-author-browser.component';
import {ZxGroupBrowserComponent} from './features/group-browser/components/zx-group-browser/zx-group-browser.component';
import {
  ZxSearchResultsComponent
} from './features/search-results/components/zx-search-results/zx-search-results.component';
import {TagsQuickFormComponent} from './features/tags-quick-form/components/tags-quick-form/tags-quick-form.component';
import {ZxProdDetailsComponent} from './features/prod-details/components/zx-prod-details/zx-prod-details.component';

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
            'zx-ratings-list': RatingsListComponent,
            'zx-firstpage': FirstpageComponent,
            'zx-player': PlayerHostComponent,
            'zx-legacy-play': LegacyPlayButtonComponent,
            'zx-author-tunes': AuthorTunesComponent,
            'zx-item-legacy-controls': ZxItemLegacyControlsComponent,
            'zx-music-list': ZxMusicListComponent,
            'zx-author-pictures': AuthorPicturesComponent,
            'zx-pictures-list': ZxPicturesListComponent,
            'zx-pictures-related': ZxPicturesRelatedComponent,
            'zx-header': ZxHeaderComponent,
            'zx-right-column': ZxRightColumnComponent,
            'zx-picture-browser': ZxPictureBrowserComponent,
            'zx-music-browser': ZxMusicBrowserComponent,
            'zx-prods-browser': ZxProdsBrowserComponent,
            'zx-author-browser': ZxAuthorBrowserComponent,
            'zx-group-browser': ZxGroupBrowserComponent,
            'zx-search-results': ZxSearchResultsComponent,
            'zx-tags-quick-form': TagsQuickFormComponent,
            'zx-prod-details': ZxProdDetailsComponent,
        } as { [key: string]: Type<Object> };
        for (const selector of Object.keys(elements)) {
            const element = createCustomElement(elements[selector], {injector: this.injector});
            customElements.define(selector, element);
        }
    }
}
