/**
 * A supported interface language. The SPA owns the language entirely, so the
 * list is a frontend constant matching the shipped i18n bundles.
 */
export interface LanguageOption {
  /** ngx-translate / i18n bundle code, e.g. `en`. */
  short: string;
  /** backend iso6393 code sent to the API and stored as the user preference, e.g. `eng`. */
  long: string;
  /** native language name shown in the switcher. */
  title: string;
  /** flag emoji shown in the switcher. */
  flag: string;
}
