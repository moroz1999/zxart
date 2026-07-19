// The SPA shell (index.spa.tpl) declares <base href="/">, but the Angular
// bundle is served from a subdirectory. Without an explicit public path webpack
// would request lazy chunks from the document base (/526.js) instead of the
// bundle directory. This must run before any dynamic import, so it is imported
// first in main.ts. The path mirrors NgAssetsProvider::WEB_BASE on the backend.
declare var __webpack_public_path__: string;
__webpack_public_path__ = '/js/ng-zxart/';
