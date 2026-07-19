import {Routes} from '@angular/router';
import {editPrivilegeGuard} from './shared/guards/edit-privilege.guard';
import {authGuard} from './shared/guards/auth.guard';

/**
 * New clean-URL routing standard (no language segment). Plural = collection,
 * singular = one entity, action = trailing segment. The entity id always comes
 * from the route param. Built out incrementally; list/collection routes follow
 * once section-root resolution lands (browsers currently require a root id).
 *
 * All routes sit under a pathless `authGuard` parent: it resolves the current
 * user (auto-login) and applies their language before the first render.
 *
 * Every page is loaded lazily via `loadComponent`/`loadChildren` so each page
 * (and its heavy dependencies) ships in its own chunk instead of the initial
 * bundle. Do not statically import page components here.
 */
const ROUTED_CHILDREN: Routes = [
  {path: 'author/:id', loadComponent: () => import('./pages/author/author-page.component').then(m => m.AuthorPageComponent), data: {metadataSource: 'entity'}},
  {
    path: 'author/:id/edit',
    loadComponent: () => import('./pages/author-edit/author-edit-page.component').then(m => m.AuthorEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'author'},
  },
  {
    path: 'author/:id/join',
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
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
    loadComponent: () => import('./pages/claim/claim-page.component').then(m => m.ClaimPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'claim'},
  },
  {
    path: 'author/:id/convert-to-group',
    loadComponent: () => import('./pages/convert-form/convert-form-page.component').then(m => m.ConvertFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'convertToGroup', action: 'convertToGroup', targetPath: 'group', messageKey: 'convert-form.author-to-group'},
  },
  {path: 'author/:id/:tab', loadComponent: () => import('./pages/author/author-page.component').then(m => m.AuthorPageComponent), data: {metadataSource: 'entity'}},
  {
    path: 'author-alias/:id/convert-to-author',
    loadComponent: () => import('./pages/convert-form/convert-form-page.component').then(m => m.ConvertFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'convertToAuthor', action: 'convertToAuthor', targetPath: 'author', messageKey: 'convert-form.alias-to-author'},
  },
  {
    path: 'author-alias/:id/edit',
    loadComponent: () => import('./pages/author-alias-edit/author-alias-edit-page.component').then(m => m.AuthorAliasEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'author'},
  },
  {
    path: 'author-alias/:id/join',
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'author',
      pickers: [{field: 'joinAndDelete', labelKey: 'join-form.join-and-delete', types: 'author,authorAlias'}],
    },
  },
  {path: 'group/:id', loadComponent: () => import('./pages/group/group-page.component').then(m => m.GroupPageComponent)},
  {
    path: 'group/:id/edit',
    loadComponent: () => import('./pages/group-edit/group-edit-page.component').then(m => m.GroupEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'group'},
  },
  {
    path: 'group/:id/join',
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
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
    loadComponent: () => import('./pages/convert-form/convert-form-page.component').then(m => m.ConvertFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'convertToAuthor', action: 'convertToAuthor', targetPath: 'author', messageKey: 'convert-form.group-to-author'},
  },
  {path: 'group/:id/:tab', loadComponent: () => import('./pages/group/group-page.component').then(m => m.GroupPageComponent)},
  {
    path: 'group-alias/:id/edit',
    loadComponent: () => import('./pages/group-edit/group-edit-page.component').then(m => m.GroupEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'group'},
  },
  {
    path: 'group-alias/:id/convert-to-group',
    loadComponent: () => import('./pages/convert-form/convert-form-page.component').then(m => m.ConvertFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'convertToGroup', action: 'convertToGroup', targetPath: 'group', messageKey: 'convert-form.alias-to-group'},
  },
  {
    path: 'group-alias/:id/join',
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'group',
      pickers: [{field: 'joinAndDelete', labelKey: 'join-form.join-and-delete', types: 'group,groupAlias'}],
    },
  },
  {path: 'party/:id', loadComponent: () => import('./pages/party/party-page.component').then(m => m.PartyPageComponent)},
  {
    path: 'party/:id/edit',
    loadComponent: () => import('./pages/party-edit/party-edit-page.component').then(m => m.PartyEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'party'},
  },
  {path: 'party/:id/:tab', loadComponent: () => import('./pages/party/party-page.component').then(m => m.PartyPageComponent)},
  {path: 'prod/:id', loadComponent: () => import('./pages/prod/prod-page.component').then(m => m.ProdPageComponent), data: {metadataSource: 'entity'}},
  {
    path: 'prod/:id/edit',
    loadComponent: () => import('./pages/prod-edit/prod-edit-page.component').then(m => m.ProdEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'prod'},
  },
  {
    path: 'prod/:id/ai',
    loadComponent: () => import('./pages/ai-form/ai-form-page.component').then(m => m.AiFormPageComponent),
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
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
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
    loadComponent: () => import('./pages/split-form/split-form-page.component').then(m => m.SplitFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'showSplitForm', entityPath: 'prod'},
  },
  {path: 'prod/:id/:tab', loadComponent: () => import('./pages/prod/prod-page.component').then(m => m.ProdPageComponent), data: {metadataSource: 'entity'}},
  {path: 'release/:id', loadComponent: () => import('./pages/release/release-page.component').then(m => m.ReleasePageComponent)},
  {
    path: 'release/:id/edit',
    loadComponent: () => import('./pages/release-edit/release-edit-page.component').then(m => m.ReleaseEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'release'},
  },
  {path: 'picture/:id', loadComponent: () => import('./pages/picture/picture-page.component').then(m => m.PicturePageComponent), data: {metadataSource: 'entity'}},
  {
    path: 'picture/:id/edit',
    loadComponent: () => import('./pages/picture-edit/picture-edit-page.component').then(m => m.PictureEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'picture'},
  },
  {path: 'tune/:id', loadComponent: () => import('./pages/tune/tune-page.component').then(m => m.TunePageComponent)},
  {
    path: 'tune/:id/edit',
    loadComponent: () => import('./pages/tune-edit/tune-edit-page.component').then(m => m.TuneEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'tune'},
  },
  {path: 'press/:id', loadComponent: () => import('./pages/press/press-page.component').then(m => m.PressPageComponent)},
  {
    path: 'press/:id/edit',
    loadComponent: () => import('./pages/press-edit/press-edit-page.component').then(m => m.PressEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {privilege: 'publicReceive', entityPath: 'press'},
  },
  {
    path: 'press/:id/ai',
    loadComponent: () => import('./pages/ai-form/ai-form-page.component').then(m => m.AiFormPageComponent),
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
  {path: 'profile', loadComponent: () => import('./pages/profile/profile-page.component').then(m => m.ProfilePageComponent)},
  {path: 'profile/edit', loadComponent: () => import('./pages/profile-edit/profile-edit-page.component').then(m => m.ProfileEditPageComponent)},
  {path: 'playlists', loadComponent: () => import('./pages/playlists/playlists-page.component').then(m => m.PlaylistsPageComponent)},
  {path: 'playlist/:id', loadComponent: () => import('./pages/playlist/playlist-page.component').then(m => m.PlaylistPageComponent)},
  {path: 'register', loadComponent: () => import('./pages/register/register-page.component').then(m => m.RegisterPageComponent)},
  {path: 'password-reminder', loadComponent: () => import('./pages/password-reminder/password-reminder-page.component').then(m => m.PasswordReminderPageComponent)},
  {path: 'search', loadComponent: () => import('./pages/search/search-page.component').then(m => m.SearchPageComponent), data: {titleKey: 'menu.gfx.search'}},
  {path: 'prods', loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent), data: {kind: 'prods', titleKey: 'menu.software'}},
  {path: 'groups', loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent), data: {kind: 'groups', titleKey: 'menu.groups'}},
  {path: 'groups/:letter', loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent), data: {kind: 'groups', titleKey: 'menu.groups'}},
  {path: 'pictures/search', loadComponent: () => import('./pages/picture-search/picture-search-page.component').then(m => m.PictureSearchPageComponent), data: {titleKey: 'menu.gfx.search'}},
  {path: 'pictures/tags', loadComponent: () => import('./pages/tags/tags-page.component').then(m => m.TagsPageComponent), data: {section: 'graphics', searchBasePath: '/pictures/search', titleKey: 'menu.gfx.tags'}},
  {path: 'pictures/top', loadComponent: () => import('./pages/top-pictures/top-pictures-page.component').then(m => m.TopPicturesPageComponent), data: {titleKey: 'menu.gfx.top'}},
  {path: 'pictures', loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent), data: {kind: 'pictures', titleKey: 'menu.graphics'}},
  {path: 'music/search', loadComponent: () => import('./pages/music-search/music-search-page.component').then(m => m.MusicSearchPageComponent), data: {titleKey: 'menu.music-sub.search'}},
  {path: 'music/tags', loadComponent: () => import('./pages/tags/tags-page.component').then(m => m.TagsPageComponent), data: {section: 'music', searchBasePath: '/music/search', titleKey: 'menu.music-sub.tags'}},
  {path: 'music/top', loadComponent: () => import('./pages/top-music/top-music-page.component').then(m => m.TopMusicPageComponent), data: {titleKey: 'menu.music-sub.top'}},
  {path: 'music', loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent), data: {kind: 'music', titleKey: 'menu.music'}},
  {path: 'authors', loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent), data: {items: '', basePath: '/authors', titleKey: 'author-browser.title.all'}},
  {path: 'authors/:letter', loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent), data: {items: '', basePath: '/authors', titleKey: 'author-browser.title.all'}},
  {path: 'artists', loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent), data: {items: 'graphics', basePath: '/artists', titleKey: 'author-browser.title.graphics', dashboard: true}},
  {path: 'artists/:letter', loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent), data: {items: 'graphics', basePath: '/artists', titleKey: 'author-browser.title.graphics'}},
  {path: 'musicians', loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent), data: {items: 'music', basePath: '/musicians', titleKey: 'author-browser.title.music', dashboard: true}},
  {path: 'musicians/:letter', loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent), data: {items: 'music', basePath: '/musicians', titleKey: 'author-browser.title.music'}},
  {path: 'parties', loadComponent: () => import('./pages/parties/parties-page.component').then(m => m.PartiesPageComponent), data: {titleKey: 'menu.parties'}},
  {path: 'parties/:year', loadComponent: () => import('./pages/parties/parties-page.component').then(m => m.PartiesPageComponent), data: {titleKey: 'menu.parties'}},
  {path: 'stats', loadChildren: () => import('./pages/stats/stats.routes').then(m => m.STATS_ROUTES), data: {titleKey: 'menu.about-sub.stats'}},
  {
    path: 'geo',
    loadComponent: () => import('./pages/countries/countries-page.component').then(m => m.CountriesPageComponent),
    data: {titleKey: 'menu.countries'},
    children: [
      {path: 'country/:id', children: [], data: {placeKind: 'country'}},
      {path: 'city/:id', children: [], data: {placeKind: 'city'}},
    ],
  },
  {path: 'comments', loadComponent: () => import('./pages/comments/comments-route-page.component').then(m => m.CommentsRoutePageComponent), data: {titleKey: 'menu.comments'}},
  {path: 'feedback', loadComponent: () => import('./pages/feedback/feedback-page.component').then(m => m.FeedbackPageComponent), data: {titleKey: 'menu.about-sub.feedback'}},
  {path: 'about', loadComponent: () => import('./pages/content/content-page.component').then(m => m.ContentPageComponent), data: {page: 'about', titleKey: 'menu.about'}},
  {path: 'about/faq', loadComponent: () => import('./pages/content/content-page.component').then(m => m.ContentPageComponent), data: {page: 'faq', titleKey: 'menu.about-sub.faq'}},
  {path: 'about/support', loadComponent: () => import('./pages/content/content-page.component').then(m => m.ContentPageComponent), data: {page: 'support', titleKey: 'menu.about-sub.support'}},
  {path: 'about/api', loadComponent: () => import('./pages/content/content-page.component').then(m => m.ContentPageComponent), data: {page: 'api', titleKey: 'menu.about-sub.api'}},
  {path: 'file-search', loadComponent: () => import('./pages/file-search/file-search-page.component').then(m => m.FileSearchPageComponent), data: {titleKey: 'menu.about-sub.filesearch'}},
  {path: '', loadComponent: () => import('./pages/firstpage/firstpage.component').then(m => m.FirstpageComponent), data: {titleKey: 'menu.home'}},
  {path: '**', loadComponent: () => import('./pages/not-found/not-found.component').then(m => m.NotFoundComponent), data: {titleKey: 'common.not-found', noIndex: true}},
];

export const APP_ROUTES: Routes = [
  {path: '', canActivateChild: [authGuard], children: ROUTED_CHILDREN},
];
