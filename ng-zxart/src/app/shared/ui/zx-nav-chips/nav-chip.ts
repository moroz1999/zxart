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
