import {Pipe, PipeTransform} from '@angular/core';

export interface ProdReleaseLabelInput {
  releaseTitle: string;
  releaseYear?: number;
  releaseTypeLabel?: string | null;
  releaseBy?: Array<{title: string}>;
}

@Pipe({name: 'prodReleaseLabel', standalone: true})
export class ProdReleaseLabelPipe implements PipeTransform {
  transform(release: ProdReleaseLabelInput): string {
    const parts: string[] = [];
    if (release.releaseBy && release.releaseBy.length > 0) {
      parts.push(release.releaseBy.map(ref => ref.title).join(', '));
    }
    if (release.releaseTypeLabel) {
      parts.push(release.releaseTypeLabel);
    }
    if (release.releaseYear && release.releaseYear > 0) {
      parts.push(String(release.releaseYear));
    }
    const suffix = parts.length > 0 ? ` (${parts.join(', ')})` : '';
    return (release.releaseTitle || '') + suffix;
  }
}
