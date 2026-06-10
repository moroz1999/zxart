/**
 * Scrolls the given element into view if its top edge is above the viewport.
 * Intended for scrolling back to the tabs bar when the user has paged down.
 */
export function scrollToElementIfHidden(el: Element | null | undefined): void {
  if (!el) return;
  if (el.getBoundingClientRect().top < 0) {
    el.scrollIntoView({behavior: 'smooth', block: 'start'});
  }
}
