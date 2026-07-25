/**
 * Action a keydown maps to over an options list (autocomplete / role picker).
 * `move` yields the next highlighted index, `select` the index to commit,
 * `close` dismisses the list, `none` means the key is not ours to handle.
 */
export type ListNavAction =
  | {readonly kind: 'move'; readonly index: number}
  | {readonly kind: 'select'; readonly index: number}
  | {readonly kind: 'close'}
  | {readonly kind: 'none'};

/**
 * Shared keyboard navigation for search dropdowns: ↑/↓ move the highlight
 * (wrapping at the ends), Enter selects the highlighted option, Escape closes.
 * Used by the entity pickers and the member-role editor so they behave alike.
 *
 * Callers should `preventDefault()` for every action except `none` — in
 * particular on Enter, to stop the surrounding form from submitting.
 */
export function listKeyboardNav(key: string, activeIndex: number, length: number): ListNavAction {
  switch (key) {
    case 'ArrowDown':
      return {kind: 'move', index: length ? (activeIndex + 1) % length : 0};
    case 'ArrowUp':
      return {kind: 'move', index: length ? (activeIndex - 1 + length) % length : 0};
    case 'Enter':
      return {kind: 'select', index: activeIndex};
    case 'Escape':
      return {kind: 'close'};
    default:
      return {kind: 'none'};
  }
}
