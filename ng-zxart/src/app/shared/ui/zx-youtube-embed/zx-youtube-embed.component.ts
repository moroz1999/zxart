import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DomSanitizer, SafeResourceUrl} from '@angular/platform-browser';

@Component({
  selector: 'zx-youtube-embed',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-youtube-embed.component.html',
  styleUrl: './zx-youtube-embed.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxYoutubeEmbedComponent {
  private _embedUrl: SafeResourceUrl | null = null;

  constructor(private sanitizer: DomSanitizer) {}

  @Input()
  set youtubeId(value: string | null | undefined) {
    this._embedUrl = value
      ? this.sanitizer.bypassSecurityTrustResourceUrl(`https://www.youtube.com/embed/${value}`)
      : null;
  }

  get embedUrl(): SafeResourceUrl | null {
    return this._embedUrl;
  }
}
