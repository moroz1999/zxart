/**
 * A hardcoded navigation entry. The menu lives entirely in the frontend (no
 * backend `/menu`). Labels come from i18n (`labelKey`) except for letters/years,
 * which are literal (`label`). Migrated sections use clean SPA routes; the rest
 * keep their working legacy URLs until those pages are migrated.
 */
export interface MenuEntry {
  /** i18n key for the label (named items). */
  labelKey?: string;
  /** literal label (letters, years). */
  label?: string;
  /** in-app SPA route or external link. */
  url: string;
  /** query params for the routerLink (e.g. category id for the prods browser). */
  queryParams?: Record<string, string | number>;
  children: MenuEntry[];
}

const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

/**
 * A–Z browse-by-letter children for a collection (`/authors`, `/groups`).
 * "#" (non-alphabetic) has no clean letter route, so it points at the full list.
 */
function letters(basePath: string): MenuEntry[] {
  const az = ALPHABET.map(letter => ({label: letter, url: `${basePath}/${letter.toLowerCase()}`, children: []}));
  return [...az, {label: '#', url: basePath, children: []}];
}

/** Party years present in the catalogue (1991–1993 have no parties). */
export const PARTY_YEARS = [
  1986, 1987, 1988, 1989, 1990, 1994, 1995, 1996, 1997, 1998, 1999,
  2000, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010,
  2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020, 2021,
  2022, 2023, 2024, 2025, 2026,
];

function years(): MenuEntry[] {
  return PARTY_YEARS.map(year => ({label: String(year), url: `/parties/${year}`, children: []}));
}

/**
 * The site's main navigation. Migrated sections point at clean SPA routes
 * (home, software, graphics/music incl. their search pages, authors, parties,
 * groups, countries, stats, comments, feedback, the full graphics/music
 * sub-views, the About/FAQ/support/API static content pages, and the file
 * search). The only remaining legacy entry is the simple-view site mode; the
 * Telegram links are external by design.
 */
export const MAIN_MENU: MenuEntry[] = [
  {labelKey: 'menu.home', url: '/', children: []},
  {
    labelKey: 'menu.software',
    url: '/prods',
    children: [
      {labelKey: 'menu.soft.demoscene', url: '/prods', queryParams: {cat: 204819}, children: []},
      {labelKey: 'menu.soft.games', url: '/prods', queryParams: {cat: 92177}, children: []},
      {labelKey: 'menu.soft.system', url: '/prods', queryParams: {cat: 92183}, children: []},
      {labelKey: 'menu.soft.misc', url: '/prods', queryParams: {cat: 92188}, children: []},
      {labelKey: 'menu.soft.educational', url: '/prods', queryParams: {cat: 92534}, children: []},
      {labelKey: 'menu.soft.compilation', url: '/prods', queryParams: {cat: 202588}, children: []},
      {labelKey: 'menu.soft.press', url: '/prods', queryParams: {cat: 244858}, children: []},
      {labelKey: 'menu.soft.applications', url: '/prods', queryParams: {cat: 244880}, children: []},
      {labelKey: 'menu.soft.series', url: '/prods', queryParams: {cat: 551860}, children: []},
      {labelKey: 'menu.soft.tags', url: '/prods/tags', children: []},
    ],
  },
  {
    labelKey: 'menu.graphics',
    url: '/pictures',
    children: [
      {labelKey: 'menu.gfx.home', url: '/pictures', children: []},
      {labelKey: 'menu.gfx.search', url: '/pictures/search', children: []},
      {labelKey: 'menu.gfx.authors', url: '/artists', children: []},
      {labelKey: 'menu.gfx.top', url: '/pictures/top', children: []},
      {labelKey: 'menu.gfx.tags', url: '/pictures/tags', children: []},
    ],
  },
  {
    labelKey: 'menu.music',
    url: '/music',
    children: [
      {labelKey: 'menu.music-sub.home', url: '/music', children: []},
      {labelKey: 'menu.music-sub.search', url: '/music/search', children: []},
      {labelKey: 'menu.music-sub.authors', url: '/musicians', children: []},
      {labelKey: 'menu.music-sub.top', url: '/music/top', children: []},
      {labelKey: 'menu.music-sub.tags', url: '/music/tags', children: []},
    ],
  },
  {labelKey: 'menu.authors', url: '/authors', children: letters('/authors')},
  {labelKey: 'menu.parties', url: '/parties', children: years()},
  {labelKey: 'menu.groups', url: '/groups', children: letters('/groups')},
  {labelKey: 'menu.comments', url: '/comments', children: []},
  {labelKey: 'menu.countries', url: '/geo', children: []},
  {
    labelKey: 'menu.about',
    url: '/about',
    children: [
      {labelKey: 'menu.about-sub.stats', url: '/stats', children: []},
      {labelKey: 'menu.about-sub.faq', url: '/about/faq', children: []},
      {labelKey: 'menu.about-sub.feedback', url: '/feedback', children: []},
      {labelKey: 'menu.about-sub.api', url: '/about/api', children: []},
      {labelKey: 'menu.about-sub.simple', url: '/simple/', children: []},
      {labelKey: 'menu.about-sub.telegram', url: 'https://t.me/zxart_ee', children: []},
      {labelKey: 'menu.about-sub.daily', url: 'https://t.me/zxartdaily', children: []},
      {labelKey: 'menu.about-sub.filesearch', url: '/file-search', children: []},
      {labelKey: 'menu.about-sub.support', url: '/about/support', children: []},
    ],
  },
];
