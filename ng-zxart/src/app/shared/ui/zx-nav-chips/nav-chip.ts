/**
 * A single chip in a {@link ZxNavChipsComponent} strip. A chip either navigates
 * (when `href` is set) or acts as a filter toggle emitting `value` on click.
 */
export interface ZxNavChip {
  readonly label: string;
  readonly active: boolean;
  /** Navigation target — when set, the chip renders as a link. */
  readonly href?: string;
  /** Payload emitted through `(chipSelect)` when a non-link chip is clicked. */
  readonly value?: string;
}

const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

/**
 * Slugs of the named subsets a collection page offers. `null` is the whole
 * collection, living at the page's own path; every other slug is the page's
 * optional trailing route segment.
 */
export type FilterSlug = string | null;

/** Translation key of one filter chip; the whole collection is labelled `all`. */
export function filterChipKey(labelPrefix: string, slug: FilterSlug): string {
  return `${labelPrefix}.${slug ?? 'all'}`;
}

/**
 * Builds the chip strip of a filtered collection page: one link chip per slug,
 * pointing at `basePath` or `basePath/<slug>`, with `activeSlug` marked.
 */
export function buildFilterChips(
  basePath: string,
  slugs: readonly FilterSlug[],
  labelPrefix: string,
  labels: Record<string, string>,
  activeSlug: FilterSlug,
): ZxNavChip[] {
  return slugs.map(slug => {
    const key = filterChipKey(labelPrefix, slug);
    return {
      label: labels[key] ?? key,
      href: slug === null ? basePath : `${basePath}/${slug}`,
      active: slug === activeSlug,
    };
  });
}

/**
 * Builds an A–Z strip of link chips pointing at `${basePath}/<letter>`.
 * The chip matching `activeLetter` (case-insensitive) is marked active.
 */
export function buildLetterChips(basePath: string, activeLetter: string): ZxNavChip[] {
  const active = activeLetter.toUpperCase();
  return ALPHABET.map(letter => ({
    label: letter,
    href: `${basePath}/${letter.toLowerCase()}`,
    active: letter === active,
  }));
}
