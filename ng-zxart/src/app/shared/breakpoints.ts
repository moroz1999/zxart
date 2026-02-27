/**
 * Project breakpoints for TypeScript code (BreakpointObserver).
 *
 * Correspond to SCSS breakpoints in `vars.scss`:
 *   xs: 320px, sm: 576px, md: 768px, lg: 992px, xl: 1200px, xxl: 1400px
 *
 * Usage with Angular CDK BreakpointObserver:
 *
 * ```typescript
 * import {BreakpointObserver} from '@angular/cdk/layout';
 * import {ZxBreakpoints} from '../shared/breakpoints';
 *
 * constructor(private bp: BreakpointObserver) {}
 *
 * ngOnInit() {
 *   this.bp.observe(ZxBreakpoints.Mobile).subscribe(state => {
 *     this.isMobile = state.matches;
 *   });
 * }
 * ```
 *
 * Docs: docs/design-system/breakpoints.md
 */
export const ZxBreakpoints = {
  /** < 320px — very small devices */
  XSmall: '(max-width: 319.98px)',

  /** < 576px — phones in portrait orientation (xs + sm in SCSS) */
  Mobile: '(max-width: 575.98px)',

  /** 576px–767px — phones in landscape / small tablet */
  MobileLandscape: '(min-width: 576px) and (max-width: 767.98px)',

  /** < 768px — all mobile devices (portrait + landscape) */
  MobileAll: '(max-width: 767.98px)',

  /** 768px–991px — tablets */
  Tablet: '(min-width: 768px) and (max-width: 991.98px)',

  /** < 992px — mobile + tablet */
  TabletDown: '(max-width: 991.98px)',

  /** >= 992px — desktop and above */
  Desktop: '(min-width: 992px)',

  /** 992px–1199px — small desktop (lg) */
  SmallDesktop: '(min-width: 992px) and (max-width: 1199.98px)',

  /** 1200px–1399px — desktop (xl) */
  LargeDesktop: '(min-width: 1200px) and (max-width: 1399.98px)',

  /** >= 1400px — wide screen (xxl) */
  XLargeDesktop: '(min-width: 1400px)',
} as const;

/**
 * Angular CDK Breakpoints — original strings from `@angular/cdk/layout`.
 *
 * Use with `BreakpointObserver.observe(CdkBreakpoints.Handset)`.
 * Based on Material Design Device Metrics (portrait/landscape separated).
 *
 * Prefer `ZxBreakpoints` (above) for project code — they align with our SCSS breakpoints.
 * These constants are for reference and compatibility with Angular Material / CDK components.
 */
export const CdkBreakpoints = {
  /** Phone portrait: < 600px */
  HandsetPortrait: '(max-width: 599.98px) and (orientation: portrait)',

  /** Phone landscape: < 960px landscape */
  HandsetLandscape: '(max-width: 959.98px) and (orientation: landscape)',

  /** Any phone (portrait + landscape) */
  Handset:
    '(max-width: 599.98px) and (orientation: portrait), ' +
    '(max-width: 959.98px) and (orientation: landscape)',

  /** Tablet portrait: 600px–839px */
  TabletPortrait: '(min-width: 600px) and (max-width: 839.98px) and (orientation: portrait)',

  /** Tablet landscape: 960px–1279px */
  TabletLandscape:
    '(min-width: 960px) and (max-width: 1279.98px) and (orientation: landscape)',

  /** Any tablet */
  Tablet:
    '(min-width: 600px) and (max-width: 839.98px) and (orientation: portrait), ' +
    '(min-width: 960px) and (max-width: 1279.98px) and (orientation: landscape)',

  /** < 600px (orientation-agnostic) */
  XSmall: '(max-width: 599.98px)',

  /** 600px–959px */
  Small: '(min-width: 600px) and (max-width: 959.98px)',

  /** 960px–1279px */
  Medium: '(min-width: 960px) and (max-width: 1279.98px)',

  /** 1280px–1919px */
  Large: '(min-width: 1280px) and (max-width: 1919.98px)',

  /** >= 1920px */
  XLarge: '(min-width: 1920px)',
} as const;
