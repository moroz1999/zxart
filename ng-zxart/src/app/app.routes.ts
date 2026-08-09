import {Routes} from '@angular/router';
import {editPrivilegeGuard} from './shared/guards/edit-privilege.guard';
import {rootPrivilegeGuard} from './shared/guards/root-privilege.guard';
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
 *
 * A page whose trailing segment is optional (an in-page tab, a browsed letter
 * or year) declares that segment as a childless child route instead of a second
 * top-level path. Two sibling paths are two route configs, and the router only
 * keeps a component alive across a navigation when the config stays the same —
 * a sibling path would tear the page down and reload all of its data on the
 * first click. The page reads such a parameter with `childRouteParam`.
 * Consequences of the nesting: the `:param` child swallows any single trailing
 * segment, so action routes of the same entity (`edit`, `join`, …) must be
 * declared before their entity route; and route data read from the deepest
 * route (`metadataSource`, `inPageTab`, `titleKey`) has to be repeated on the
 * child.
 */
const ROUTED_CHILDREN: Routes = [
  {
    path: 'authors/add',
    loadComponent: () => import('./pages/author-edit/author-edit-page.component').then(m => m.AuthorEditPageComponent),
    data: {create: true, titleKey: 'author-browser.add-author'},
  },
  {
    path: 'authors/:letter/add',
    loadComponent: () => import('./pages/author-edit/author-edit-page.component').then(m => m.AuthorEditPageComponent),
    data: {create: true, titleKey: 'author-browser.add-author'},
  },
  {
    path: 'author/:id/aliases/add',
    loadComponent: () => import('./pages/author-alias-edit/author-alias-edit-page.component').then(m => m.AuthorAliasEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      create: true,
      privilege: 'authorAlias.showPublicForm',
      entityPath: 'author',
      titleKey: 'author-details.action.add-alias',
    },
  },
  {
    path: 'author/:id/pictures/add',
    loadComponent: () => import('./pages/picture-edit/picture-edit-page.component').then(m => m.PictureEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      batchUpload: true,
      privilege: 'picturesUploadForm.batchUploadForm',
      entityPath: 'author',
      titleKey: 'picture-form.batch-title',
    },
  },
  {
    path: 'author/:id/music/add',
    loadComponent: () => import('./pages/tune-edit/tune-edit-page.component').then(m => m.TuneEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      batchUpload: true,
      privilege: 'musicUploadForm.batchUploadForm',
      entityPath: 'author',
      titleKey: 'tune-form.batch-title',
    },
  },
  {
    path: 'author/:id/prods/add',
    loadComponent: () => import('./pages/prod-edit/prod-edit-page.component').then(m => m.ProdEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      batchUpload: true,
      privilege: 'zxProdsUploadForm.batchUploadForm',
      entityPath: 'author',
      titleKey: 'author-details.action.upload-prods',
    },
  },
  {
    path: 'group/:id/prods/add',
    loadComponent: () => import('./pages/prod-edit/prod-edit-page.component').then(m => m.ProdEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      batchUpload: true,
      privilege: 'zxProdsUploadForm.batchUploadForm',
      entityPath: 'group',
      titleKey: 'group-details.action.upload-prods',
    },
  },
  {
    path: 'party/:id/pictures/add',
    loadComponent: () => import('./pages/picture-edit/picture-edit-page.component').then(m => m.PictureEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      batchUpload: true,
      privilege: 'picturesUploadForm.batchUploadForm',
      entityPath: 'party',
      titleKey: 'picture-form.batch-title',
    },
  },
  {
    path: 'party/:id/music/add',
    loadComponent: () => import('./pages/tune-edit/tune-edit-page.component').then(m => m.TuneEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      batchUpload: true,
      privilege: 'musicUploadForm.batchUploadForm',
      entityPath: 'party',
      titleKey: 'tune-form.batch-title',
    },
  },
  {
    path: 'party/:id/prods/add',
    loadComponent: () => import('./pages/prod-edit/prod-edit-page.component').then(m => m.ProdEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      batchUpload: true,
      privilege: 'zxProdsUploadForm.batchUploadForm',
      entityPath: 'party',
      titleKey: 'party-details.action.upload-prods',
    },
  },
  {
    path: 'prod/:id/releases/add',
    loadComponent: () => import('./pages/release-edit/release-edit-page.component').then(m => m.ReleaseEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      create: true,
      privilege: 'zxRelease.publicAdd',
      entityPath: 'prod',
      titleKey: 'prod-details.addrelease',
    },
  },
  {
    path: 'prod/:id/articles/add',
    loadComponent: () => import('./pages/press-edit/press-edit-page.component').then(m => m.PressEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      create: true,
      privilege: 'pressArticle.publicReceive',
      entityPath: 'prod',
      titleKey: 'prod-details.addpressarticle',
      formTitleKey: 'form-page-title.add-press',
    },
  },
  {
    path: 'groups/add',
    loadComponent: () => import('./pages/group-edit/group-edit-page.component').then(m => m.GroupEditPageComponent),
    data: {create: true, titleKey: 'group-browser.add-group'},
  },
  {
    path: 'groups/:letter/add',
    loadComponent: () => import('./pages/group-edit/group-edit-page.component').then(m => m.GroupEditPageComponent),
    data: {create: true, titleKey: 'group-browser.add-group'},
  },
  {
    path: 'parties/:year/add',
    loadComponent: () => import('./pages/party-edit/party-edit-page.component').then(m => m.PartyEditPageComponent),
    data: {create: true, titleKey: 'parties-page.add-party'},
  },
  {
    path: 'prods/batch-upload',
    loadComponent: () => import('./pages/prod-edit/prod-edit-page.component').then(m => m.ProdEditPageComponent),
    data: {batchUpload: true, titleKey: 'prods-list.batch-upload'},
  },
  {
    path: 'author/:id/edit',
    loadComponent: () => import('./pages/author-edit/author-edit-page.component').then(m => m.AuthorEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'author',
      titleKey: 'author-details.action.showPublicForm',
      formTitleKey: 'form-page-title.edit-author',
    },
  },
  {
    path: 'author/:id/join',
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'author',
      titleKey: 'join-form.submit',
      pickers: [
        {field: 'joinAsAlias', labelKey: 'join-form.author-as-alias', types: 'author,authorAlias'},
        {field: 'joinAndDelete', labelKey: 'join-form.author-merge', types: 'author,authorAlias'},
      ],
    },
  },
  {
    path: 'author/:id',
    loadComponent: () => import('./pages/author/author-page.component').then(m => m.AuthorPageComponent),
    data: {metadataSource: 'entity'},
    children: [{path: ':tab', children: [], data: {metadataSource: 'entity', inPageTab: true}}],
  },
  {
    path: 'author-alias/:id/edit',
    loadComponent: () => import('./pages/author-alias-edit/author-alias-edit-page.component').then(m => m.AuthorAliasEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'author',
      titleKey: 'author-details.action.showPublicForm',
      formTitleKey: 'form-page-title.edit-author-alias',
    },
  },
  {
    path: 'author-alias/:id/join',
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'author',
      titleKey: 'join-form.submit',
      pickers: [{field: 'joinAndDelete', labelKey: 'join-form.author-merge', types: 'author,authorAlias'}],
    },
  },
  {
    path: 'group/:id/edit',
    loadComponent: () => import('./pages/group-edit/group-edit-page.component').then(m => m.GroupEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'group',
      titleKey: 'group-details.action.showPublicForm',
      formTitleKey: 'form-page-title.edit-group',
    },
  },
  {
    path: 'group/:id/join',
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'group',
      titleKey: 'join-form.submit',
      pickers: [
        {field: 'joinAsAlias', labelKey: 'join-form.group-as-alias', types: 'group,groupAlias'},
        {field: 'joinAndDelete', labelKey: 'join-form.group-merge', types: 'group,groupAlias'},
      ],
    },
  },
  {
    path: 'group/:id',
    loadComponent: () => import('./pages/group/group-page.component').then(m => m.GroupPageComponent),
    data: {metadataSource: 'entity'},
    children: [{path: ':tab', children: [], data: {metadataSource: 'entity', inPageTab: true}}],
  },
  {
    path: 'group-alias/:id/edit',
    loadComponent: () => import('./pages/group-edit/group-edit-page.component').then(m => m.GroupEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'group',
      titleKey: 'group-details.action.showPublicForm',
      formTitleKey: 'form-page-title.edit-group-alias',
    },
  },
  {
    path: 'group-alias/:id/join',
    loadComponent: () => import('./pages/join-form/join-form-page.component').then(m => m.JoinFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showJoinForm',
      entityPath: 'group',
      titleKey: 'join-form.submit',
      pickers: [{field: 'joinAndDelete', labelKey: 'join-form.group-merge', types: 'group,groupAlias'}],
    },
  },
  {
    path: 'party/:id/edit',
    loadComponent: () => import('./pages/party-edit/party-edit-page.component').then(m => m.PartyEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'party',
      titleKey: 'party-details.action.showPublicForm',
      formTitleKey: 'form-page-title.edit-party',
    },
  },
  {
    path: 'party/:id',
    loadComponent: () => import('./pages/party/party-page.component').then(m => m.PartyPageComponent),
    data: {metadataSource: 'entity'},
    children: [{path: ':tab', children: [], data: {metadataSource: 'entity', inPageTab: true}}],
  },
  {
    path: 'prod/:id/edit',
    loadComponent: () => import('./pages/prod-edit/prod-edit-page.component').then(m => m.ProdEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'prod',
      titleKey: 'prod-details.edit',
      formTitleKey: 'form-page-title.edit-prod',
    },
  },
  {
    path: 'prod/:id/ai',
    loadComponent: () => import('./pages/ai-form/ai-form-page.component').then(m => m.AiFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showAiForm',
      entityPath: 'prod',
      titleKey: 'ai-form.title',
      formTitleKey: 'form-page-title.ai-prod',
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
      titleKey: 'join-form.submit',
      pickers: [{field: 'joinAndDelete', labelKey: 'join-form.prod-merge', types: 'zxProd'}],
      checkboxes: [{field: 'releasesOnly', labelKey: 'join-form.releases-only'}],
    },
  },
  {
    path: 'prod/:id/split',
    loadComponent: () => import('./pages/split-form/split-form-page.component').then(m => m.SplitFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showSplitForm',
      entityPath: 'prod',
      titleKey: 'split-form.submit',
      formTitleKey: 'form-page-title.split-prod',
    },
  },
  {
    path: 'prod/:id',
    loadComponent: () => import('./pages/prod/prod-page.component').then(m => m.ProdPageComponent),
    data: {metadataSource: 'entity'},
    children: [{path: ':tab', children: [], data: {metadataSource: 'entity', inPageTab: true}}],
  },
  {path: 'release/:id', loadComponent: () => import('./pages/release/release-page.component').then(m => m.ReleasePageComponent), data: {metadataSource: 'entity'}},
  {
    path: 'release/:id/edit',
    loadComponent: () => import('./pages/release-edit/release-edit-page.component').then(m => m.ReleaseEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'release',
      titleKey: 'release-details.edit',
      formTitleKey: 'form-page-title.edit-release',
    },
  },
  {path: 'picture/:id', loadComponent: () => import('./pages/picture/picture-page.component').then(m => m.PicturePageComponent), data: {metadataSource: 'entity'}},
  {
    path: 'picture/:id/edit',
    loadComponent: () => import('./pages/picture-edit/picture-edit-page.component').then(m => m.PictureEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'picture',
      titleKey: 'picture-details.edit',
      formTitleKey: 'form-page-title.edit-picture',
    },
  },
  {path: 'tune/:id', loadComponent: () => import('./pages/tune/tune-page.component').then(m => m.TunePageComponent), data: {metadataSource: 'entity'}},
  {
    path: 'tune/:id/edit',
    loadComponent: () => import('./pages/tune-edit/tune-edit-page.component').then(m => m.TuneEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'tune',
      titleKey: 'tune-details.edit',
      formTitleKey: 'form-page-title.edit-tune',
    },
  },
  {path: 'press/:id', loadComponent: () => import('./pages/press/press-page.component').then(m => m.PressPageComponent), data: {metadataSource: 'entity'}},
  {
    path: 'press/:id/edit',
    loadComponent: () => import('./pages/press-edit/press-edit-page.component').then(m => m.PressEditPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'publicReceive',
      entityPath: 'press',
      titleKey: 'press-details.edit',
      formTitleKey: 'form-page-title.edit-press',
    },
  },
  {
    path: 'press/:id/ai',
    loadComponent: () => import('./pages/ai-form/ai-form-page.component').then(m => m.AiFormPageComponent),
    canActivate: [editPrivilegeGuard],
    data: {
      privilege: 'showAiForm',
      entityPath: 'press',
      titleKey: 'ai-form.title',
      formTitleKey: 'form-page-title.ai-press',
      fields: [
        {field: 'aiRestartFix', labelKey: 'ai-form.press-fix'},
        {field: 'aiRestartTranslate', labelKey: 'ai-form.press-translate'},
        {field: 'aiRestartParse', labelKey: 'ai-form.press-parse'},
        {field: 'aiRestartSeo', labelKey: 'ai-form.press-seo'},
      ],
    },
  },
  {path: 'profile', loadComponent: () => import('./pages/profile/profile-page.component').then(m => m.ProfilePageComponent), data: {titleKey: 'profile.title'}},
  {path: 'profile/edit', redirectTo: 'profile', pathMatch: 'full'},
  {path: 'playlists', loadComponent: () => import('./pages/playlists/playlists-page.component').then(m => m.PlaylistsPageComponent), data: {titleKey: 'playlists.title'}},
  {path: 'playlist/:id', loadComponent: () => import('./pages/playlist/playlist-page.component').then(m => m.PlaylistPageComponent), data: {titleKey: 'playlists.playlist-title'}},
  {path: 'register', loadComponent: () => import('./pages/register/register-page.component').then(m => m.RegisterPageComponent), data: {titleKey: 'register.title'}},
  {path: 'password-reminder', loadComponent: () => import('./pages/password-reminder/password-reminder-page.component').then(m => m.PasswordReminderPageComponent), data: {titleKey: 'password-reminder.title'}},
  {path: 'search', loadComponent: () => import('./pages/search/search-page.component').then(m => m.SearchPageComponent), data: {titleKey: 'menu.gfx.search'}},
  {path: 'prods/tags', loadComponent: () => import('./pages/tags/tags-page.component').then(m => m.TagsPageComponent), data: {section: 'software', tagBasePath: '/prods/tags', titleKey: 'menu.soft.tags'}},
  {path: 'prods/tags/:id', loadComponent: () => import('./pages/prod-tag/prod-tag-page.component').then(m => m.ProdTagPageComponent), data: {section: 'software', basePath: '/prods/tags', titleKey: 'menu.soft.tags'}},
  {path: 'prods', loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent), data: {kind: 'prods', titleKey: 'menu.software'}},
  {
    path: 'groups',
    loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent),
    data: {kind: 'groups', titleKey: 'menu.groups'},
    children: [{path: ':letter', children: [], data: {titleKey: 'menu.groups'}}],
  },
  {path: 'pictures/search', loadComponent: () => import('./pages/picture-search/picture-search-page.component').then(m => m.PictureSearchPageComponent), data: {titleKey: 'menu.gfx.search'}},
  {path: 'pictures/tags', loadComponent: () => import('./pages/tags/tags-page.component').then(m => m.TagsPageComponent), data: {section: 'graphics', tagBasePath: '/pictures/tags', titleKey: 'menu.gfx.tags'}},
  {path: 'pictures/tags/:id', loadComponent: () => import('./pages/picture-tag/picture-tag-page.component').then(m => m.PictureTagPageComponent), data: {section: 'graphics', basePath: '/pictures/tags', titleKey: 'menu.gfx.tags'}},
  {path: 'pictures/top', loadComponent: () => import('./pages/top-pictures/top-pictures-page.component').then(m => m.TopPicturesPageComponent), data: {titleKey: 'menu.gfx.top'}},
  {path: 'pictures', loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent), data: {kind: 'pictures', titleKey: 'menu.graphics'}},
  {path: 'music/search', loadComponent: () => import('./pages/music-search/music-search-page.component').then(m => m.MusicSearchPageComponent), data: {titleKey: 'menu.music-sub.search'}},
  {path: 'music/tags', loadComponent: () => import('./pages/tags/tags-page.component').then(m => m.TagsPageComponent), data: {section: 'music', tagBasePath: '/music/tags', titleKey: 'menu.music-sub.tags'}},
  {path: 'music/tags/:id', loadComponent: () => import('./pages/music-tag/music-tag-page.component').then(m => m.MusicTagPageComponent), data: {section: 'music', basePath: '/music/tags', titleKey: 'menu.music-sub.tags'}},
  {path: 'music/top', loadComponent: () => import('./pages/top-music/top-music-page.component').then(m => m.TopMusicPageComponent), data: {titleKey: 'menu.music-sub.top'}},
  {path: 'music', loadComponent: () => import('./pages/collection/collection-page.component').then(m => m.CollectionPageComponent), data: {kind: 'music', titleKey: 'menu.music'}},
  {
    path: 'authors',
    loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent),
    data: {items: '', basePath: '/authors', titleKey: 'author-browser.title.all'},
    children: [{path: ':letter', children: [], data: {titleKey: 'author-browser.title.all'}}],
  },
  {
    path: 'artists',
    loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent),
    data: {items: 'graphics', basePath: '/artists', titleKey: 'author-browser.title.graphics', dashboard: true},
    children: [{path: ':letter', children: [], data: {titleKey: 'author-browser.title.graphics'}}],
  },
  {
    path: 'musicians',
    loadComponent: () => import('./pages/authors/authors-page.component').then(m => m.AuthorsPageComponent),
    data: {items: 'music', basePath: '/musicians', titleKey: 'author-browser.title.music', dashboard: true},
    children: [{path: ':letter', children: [], data: {titleKey: 'author-browser.title.music'}}],
  },
  {
    path: 'parties',
    loadComponent: () => import('./pages/parties/parties-page.component').then(m => m.PartiesPageComponent),
    data: {titleKey: 'menu.parties'},
    children: [{path: ':year', children: [], data: {titleKey: 'menu.parties'}}],
  },
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
  // Site management. `/admin` belongs to the legacy Smarty panel, so this lives at
  // /manage. Every screen is gated on a site-wide privilege held on the public root.
  {
    path: 'manage/hardware/add',
    loadComponent: () => import('./pages/manage-hardware-edit/manage-hardware-edit-page.component').then(m => m.ManageHardwareEditPageComponent),
    canActivate: [rootPrivilegeGuard],
    data: {create: true, privilege: 'editHardware', titleKey: 'manage-hardware.add', noIndex: true},
  },
  {
    path: 'manage/hardware/:id',
    loadComponent: () => import('./pages/manage-hardware-edit/manage-hardware-edit-page.component').then(m => m.ManageHardwareEditPageComponent),
    canActivate: [rootPrivilegeGuard],
    data: {privilege: 'editHardware', titleKey: 'manage-hardware.edit-title', noIndex: true},
  },
  {
    path: 'manage/hardware',
    loadComponent: () => import('./pages/manage-hardware/manage-hardware-page.component').then(m => m.ManageHardwarePageComponent),
    canActivate: [rootPrivilegeGuard],
    data: {privilege: 'editHardware', titleKey: 'manage-hardware.title', noIndex: true},
  },
  {path: 'manage', redirectTo: 'manage/hardware', pathMatch: 'full'},
  {path: 'file-search', loadComponent: () => import('./pages/file-search/file-search-page.component').then(m => m.FileSearchPageComponent), data: {titleKey: 'menu.about-sub.filesearch'}},
  {path: '', loadComponent: () => import('./pages/firstpage/firstpage.component').then(m => m.FirstpageComponent), data: {titleKey: 'menu.home', serverHomePageTitle: true}},
  {path: '**', loadComponent: () => import('./pages/not-found/not-found.component').then(m => m.NotFoundComponent), data: {titleKey: 'common.not-found', noIndex: true}},
];

export const APP_ROUTES: Routes = [
  {path: '', canActivateChild: [authGuard], children: ROUTED_CHILDREN},
];
