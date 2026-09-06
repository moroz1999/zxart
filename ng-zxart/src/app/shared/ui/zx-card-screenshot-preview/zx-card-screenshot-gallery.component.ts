import {ChangeDetectionStrategy, Component, Input, OnChanges, SimpleChanges} from '@angular/core';
import {NgClass, NgFor, NgIf} from '@angular/common';

@Component({
  selector: 'zx-card-screenshot-gallery',
  standalone: true,
  imports: [NgFor, NgIf, NgClass],
  templateUrl: './zx-card-screenshot-gallery.component.html',
  styleUrls: ['./zx-card-screenshot-gallery.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCardScreenshotGalleryComponent implements OnChanges {
  @Input({required: true}) imageUrls!: string[];
  @Input() alt = '';

  activeUrl = '';

  /**
   * What is shown follows the urls, not the array holding them. A host that
   * rebuilds that array on every check would otherwise send the gallery back to
   * the first shot on the very change detection the hover triggers, so the
   * selector would look alive and nothing would ever change.
   */
  ngOnChanges(changes: SimpleChanges): void {
    if (changes['imageUrls'] && !this.imageUrls.includes(this.activeUrl)) {
      this.activeUrl = this.imageUrls[0] ?? '';
    }
  }

  trackByUrl(_index: number, url: string): string {
    return url;
  }

  setActive(url: string): void {
    this.activeUrl = url;
  }

  reset(): void {
    this.activeUrl = this.imageUrls[0] ?? '';
  }
}
