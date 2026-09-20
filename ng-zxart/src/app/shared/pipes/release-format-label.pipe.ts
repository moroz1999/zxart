import {Pipe, PipeTransform} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';

/**
 * What a release format is called. The names live in this app's i18n files
 * under `release-format.<code>`; a code with no name there shows as itself.
 *
 * Impure because the name changes with the language, and a format chip is not
 * redrawn by anything else.
 */
@Pipe({name: 'releaseFormatLabel', standalone: true, pure: false})
export class ReleaseFormatLabelPipe implements PipeTransform {
  constructor(private readonly translate: TranslateService) {}

  transform(format: {format: string}): string {
    const key = `release-format.${format.format}`;
    const translated = this.translate.instant(key);
    return translated === key ? format.format : translated;
  }
}
