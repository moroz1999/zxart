import {Pipe, PipeTransform} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';

@Pipe({
  name: 'statsNumber',
  standalone: true,
  pure: false,
})
export class StatsNumberPipe implements PipeTransform {
  private readonly localeByLanguage: Record<string, string> = {
    en: 'en-US',
    ru: 'ru-RU',
    es: 'es-ES',
  };

  constructor(private readonly translate: TranslateService) {}

  transform(value: number | null | undefined): string {
    if (value === null || value === undefined) {
      return '';
    }

    const language = this.translate.currentLang || this.translate.getDefaultLang() || 'en';
    const locale = this.localeByLanguage[language] ?? language;

    return new Intl.NumberFormat(locale, {maximumFractionDigits: 0}).format(value);
  }
}
