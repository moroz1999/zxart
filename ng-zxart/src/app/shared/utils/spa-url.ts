/** First path segments owned by the Angular SPA router (mirrors SpaRouter on the backend). */
const SPA_SEGMENTS = new Set<string>([
  'author', 'author-alias', 'group', 'group-alias', 'party', 'prod', 'release',
  'picture', 'tune', 'press', 'profile', 'playlists', 'playlist', 'register',
  'prods', 'groups', 'pictures', 'music', 'authors', 'artists', 'musicians', 'parties', 'stats', 'geo', 'comments', 'feedback', 'about',
  'file-search',
]);

/**
 * Whether a URL points to an in-app SPA route (so it can use `routerLink`
 * instead of a full-page `href`). Same-origin absolute paths only; external
 * URLs, protocol-relative (`//host`) and legacy paths fall through to `href`.
 */
export function isSpaUrl(url: string | null | undefined): boolean {
  if (!url || !url.startsWith('/') || url.startsWith('//')) {
    return false;
  }
  if (url === '/') {
    return true; // home
  }
  const segment = url.split(/[/?#]/)[1] ?? '';
  return SPA_SEGMENTS.has(segment);
}
