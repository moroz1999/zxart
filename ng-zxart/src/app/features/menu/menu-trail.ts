import {MAIN_MENU, MenuEntry} from './menu.config';

/**
 * The ancestor→node chain of a menu entry, root-first and inclusive of the
 * matched node itself (the last element). Used to build breadcrumbs that mirror
 * the top menu structure.
 */
export type MenuTrail = MenuEntry[];

function queryParamsMatch(entry: MenuEntry, query: Record<string, string>): boolean {
  if (!entry.queryParams) {
    return true;
  }
  return Object.entries(entry.queryParams).every(([key, value]) => query[key] === String(value));
}

function declaredParamCount(entry: MenuEntry): number {
  return entry.queryParams ? Object.keys(entry.queryParams).length : 0;
}

/**
 * Finds the menu entry that the given internal path (+ query params) points at
 * and returns its full ancestor chain. When several entries share a `url` (e.g.
 * `/prods` and `/prods?cat=…`), the most specific one — the one declaring the
 * most matching query params — wins, so `/prods?cat=92177` resolves to the
 * "Games" child rather than the "Software" root. External links are ignored.
 *
 * Returns `null` when nothing in the menu matches the path (e.g. entity detail
 * pages, which supply their own trail).
 */
export function findMenuTrail(path: string, query: Record<string, string> = {}): MenuTrail | null {
  let best: MenuTrail | null = null;
  let bestScore = -1;

  const walk = (entries: MenuEntry[], ancestors: MenuEntry[]): void => {
    for (const entry of entries) {
      const chain = [...ancestors, entry];
      if (entry.url.startsWith('/') && entry.url === path && queryParamsMatch(entry, query)) {
        const score = declaredParamCount(entry);
        if (score > bestScore) {
          best = chain;
          bestScore = score;
        }
      }
      if (entry.children.length) {
        walk(entry.children, chain);
      }
    }
  };

  walk(MAIN_MENU, []);
  return best;
}
