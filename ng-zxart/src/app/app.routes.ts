import {Routes} from '@angular/router';
import {AuthorPageComponent} from './pages/author/author-page.component';
import {GroupPageComponent} from './pages/group/group-page.component';
import {PartyPageComponent} from './pages/party/party-page.component';
import {ProdPageComponent} from './pages/prod/prod-page.component';
import {ReleasePageComponent} from './pages/release/release-page.component';
import {PicturePageComponent} from './pages/picture/picture-page.component';
import {TunePageComponent} from './pages/tune/tune-page.component';
import {PartyEditPageComponent} from './pages/party-edit/party-edit-page.component';
import {AuthorAliasEditPageComponent} from './pages/author-alias-edit/author-alias-edit-page.component';
import {AuthorEditPageComponent} from './pages/author-edit/author-edit-page.component';
import {GroupEditPageComponent} from './pages/group-edit/group-edit-page.component';
import {ReleaseEditPageComponent} from './pages/release-edit/release-edit-page.component';
import {ProdEditPageComponent} from './pages/prod-edit/prod-edit-page.component';
import {TuneEditPageComponent} from './pages/tune-edit/tune-edit-page.component';
import {PictureEditPageComponent} from './pages/picture-edit/picture-edit-page.component';
import {PressPageComponent} from './pages/press/press-page.component';
import {PressEditPageComponent} from './pages/press-edit/press-edit-page.component';
import {AiFormPageComponent} from './pages/ai-form/ai-form-page.component';
import {ProfilePageComponent} from './pages/profile/profile-page.component';
import {ProfileEditPageComponent} from './pages/profile-edit/profile-edit-page.component';
import {PlaylistsPageComponent} from './pages/playlists/playlists-page.component';
import {PlaylistPageComponent} from './pages/playlist/playlist-page.component';
import {RegisterPageComponent} from './pages/register/register-page.component';
import {JoinFormPageComponent} from './pages/join-form/join-form-page.component';
import {ConvertFormPageComponent} from './pages/convert-form/convert-form-page.component';
import {SplitFormPageComponent} from './pages/split-form/split-form-page.component';
import {ClaimPageComponent} from './pages/claim/claim-page.component';
import {CollectionPageComponent} from './pages/collection/collection-page.component';
import {PictureSearchPageComponent} from './pages/picture-search/picture-search-page.component';
import {MusicSearchPageComponent} from './pages/music-search/music-search-page.component';
import {StatsPageComponent} from './pages/stats/stats-page.component';
import {CountriesPageComponent} from './pages/countries/countries-page.component';
import {CommentsRoutePageComponent} from './pages/comments/comments-route-page.component';
import {TagsPageComponent} from './pages/tags/tags-page.component';
import {FeedbackPageComponent} from './pages/feedback/feedback-page.component';
import {ContentPageComponent} from './pages/content/content-page.component';
import {FileSearchPageComponent} from './pages/file-search/file-search-page.component';
import {PartiesPageComponent} from './pages/parties/parties-page.component';
import {editPrivilegeGuard} from './shared/guards/edit-privilege.guard';
import {authGuard} from './shared/guards/auth.guard';
import {NotFoundComponent} from './pages/not-found/not-found.component';
import {FirstpageComponent} from './pages/firstpage/firstpage.component';

/**
 * New clean-URL routing standard (no language segment). Plural = collection,
 * singular = one entity, action = trailing segment. The entity id always comes
 * from the route param. Built out incrementally; list/collection routes follow
 * once section-root resolution lands (browsers currently require a root id).
 *
 * All routes sit under a pathless `authGuard` parent: it resolves the current
 * user (auto-login) and applies their language before the first render.
 */
const ROUTED_CHILDREN: Routes = [
  {path: 'author/:id', component: AuthorPageComponent},
  {
    path: 'author/:id/edit',
    component: AuthorEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'author'},
  },
  {
    path: 'author/:id/join',
    component: JoinFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'author',
      pickers: [
        {field: 'joinAsAlias', labelKey: 'join-form.join-as-alias', types: 'author,authorAlias'},
        {field: 'joinAndDelete', labelKey: 'join-form.join-and-delete', types: 'author,authorAlias'},
      ],
    },
  },
  {
    path: 'author/:id/claim',
    component: ClaimPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'claim'},
  },
  {
    path: 'author/:id/convert-to-group',
    component: ConvertFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'convertToGroup', action: 'convertToGroup', targetPath: 'group', messageKey: 'convert-form.author-to-group'},
  },
  {path: 'author/:id/:tab', component: AuthorPageComponent},
  {
    path: 'author-alias/:id/convert-to-author',
    component: ConvertFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'convertToAuthor', action: 'convertToAuthor', targetPath: 'author', messageKey: 'convert-form.alias-to-author'},
  },
  {
    path: 'author-alias/:id/edit',
    component: AuthorAliasEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'author'},
  },
  {
    path: 'author-alias/:id/join',
    component: JoinFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'author',
      pickers: [{field: 'joinAndDelete', labelKey: 'join-form.join-and-delete', types: 'author,authorAlias'}],
    },
  },
  {path: 'group/:id', component: GroupPageComponent},
  {
    path: 'group/:id/edit',
    component: GroupEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'group'},
  },
  {
    path: 'group/:id/join',
    component: JoinFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'group',
      pickers: [
        {field: 'joinAsAlias', labelKey: 'join-form.join-as-alias', types: 'group,groupAlias'},
        {field: 'joinAndDelete', labelKey: 'join-form.join-and-delete', types: 'group,groupAlias'},
      ],
    },
  },
  {
    path: 'group/:id/convert-to-author',
    component: ConvertFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'convertToAuthor', action: 'convertToAuthor', targetPath: 'author', messageKey: 'convert-form.group-to-author'},
  },
  {path: 'group/:id/:tab', component: GroupPageComponent},
  {
    path: 'group-alias/:id/edit',
    component: GroupEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'group'},
  },
  {
    path: 'group-alias/:id/convert-to-group',
    component: ConvertFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'convertToGroup', action: 'convertToGroup', targetPath: 'group', messageKey: 'convert-form.alias-to-group'},
  },
  {
    path: 'group-alias/:id/join',
    component: JoinFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'group',
      pickers: [{field: 'joinAndDelete', labelKey: 'join-form.join-and-delete', types: 'group,groupAlias'}],
    },
  },
  {path: 'party/:id', component: PartyPageComponent},
  {
    path: 'party/:id/edit',
    component: PartyEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'party'},
  },
  {path: 'party/:id/:tab', component: PartyPageComponent},
  {path: 'prod/:id', component: ProdPageComponent},
  {
    path: 'prod/:id/edit',
    component: ProdEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'prod'},
  },
  {
    path: 'prod/:id/ai',
    component: AiFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showAiForm',
      entityPath: 'prod',
      fields: [
        {field: 'aiRestartSeo', labelKey: 'ai-form.prod-seo'},
        {field: 'aiRestartIntro', labelKey: 'ai-form.prod-intro'},
        {field: 'aiRestartCategories', labelKey: 'ai-form.prod-categories'},
      ],
    },
  },
  {
    path: 'prod/:id/join',
    component: JoinFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'prod',
      pickers: [{field: 'joinAndDelete', labelKey: 'join-form.join-and-delete', types: 'zxProd'}],
      checkboxes: [{field: 'releasesOnly', labelKey: 'join-form.releases-only'}],
    },
  },
  {
    path: 'prod/:id/split',
    component: SplitFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'showSplitForm', entityPath: 'prod'},
  },
  {path: 'prod/:id/:tab', component: ProdPageComponent},
  {path: 'release/:id', component: ReleasePageComponent},
  {
    path: 'release/:id/edit',
    component: ReleaseEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'release'},
  },
  {path: 'picture/:id', component: PicturePageComponent},
  {
    path: 'picture/:id/edit',
    component: PictureEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'picture'},
  },
  {path: 'tune/:id', component: TunePageComponent},
  {
    path: 'tune/:id/edit',
    component: TuneEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'tune'},
  },
  {path: 'press/:id', component: PressPageComponent},
  {
    path: 'press/:id/edit',
    component: PressEditPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'press'},
  },
  {
    path: 'press/:id/ai',
    component: AiFormPageComponent,
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showAiForm',
      entityPath: 'press',
      fields: [
        {field: 'aiRestartFix', labelKey: 'ai-form.press-fix'},
        {field: 'aiRestartTranslate', labelKey: 'ai-form.press-translate'},
        {field: 'aiRestartParse', labelKey: 'ai-form.press-parse'},
        {field: 'aiRestartSeo', labelKey: 'ai-form.press-seo'},
      ],
    },
  },
  {path: 'profile', component: ProfilePageComponent},
  {path: 'profile/edit', component: ProfileEditPageComponent},
  {path: 'playlists', component: PlaylistsPageComponent},
  {path: 'playlist/:id', component: PlaylistPageComponent},
  {path: 'register', component: RegisterPageComponent},
  {path: 'prods', component: CollectionPageComponent, data: {kind: 'prods'}},
  {path: 'groups', component: CollectionPageComponent, data: {kind: 'groups'}},
  {path: 'groups/:letter', component: CollectionPageComponent, data: {kind: 'groups'}},
  {path: 'pictures/search', component: PictureSearchPageComponent},
  {path: 'pictures/tags', component: TagsPageComponent, data: {section: 'graphics', searchBasePath: '/pictures/search'}},
  {path: 'pictures', component: CollectionPageComponent, data: {kind: 'pictures'}},
  {path: 'music/search', component: MusicSearchPageComponent},
  {path: 'music/tags', component: TagsPageComponent, data: {section: 'music', searchBasePath: '/music/search'}},
  {path: 'music', component: CollectionPageComponent, data: {kind: 'music'}},
  {path: 'authors', component: CollectionPageComponent, data: {kind: 'authors'}},
  {path: 'authors/:letter', component: CollectionPageComponent, data: {kind: 'authors'}},
  {path: 'parties', component: PartiesPageComponent},
  {path: 'parties/:year', component: PartiesPageComponent},
  {path: 'stats', component: StatsPageComponent},
  {path: 'geo', component: CountriesPageComponent},
  {path: 'comments', component: CommentsRoutePageComponent},
  {path: 'feedback', component: FeedbackPageComponent},
  {path: 'about', component: ContentPageComponent, data: {page: 'about', titleKey: 'menu.about'}},
  {path: 'about/faq', component: ContentPageComponent, data: {page: 'faq', titleKey: 'menu.about-sub.faq'}},
  {path: 'about/support', component: ContentPageComponent, data: {page: 'support', titleKey: 'menu.about-sub.support'}},
  {path: 'about/api', component: ContentPageComponent, data: {page: 'api', titleKey: 'menu.about-sub.api'}},
  {path: 'file-search', component: FileSearchPageComponent},
  {path: '', component: FirstpageComponent},
  {path: '**', component: NotFoundComponent},
];

export const APP_ROUTES: Routes = [
  {path: '', canActivateChild: [authGuard], children: ROUTED_CHILDREN},
];
