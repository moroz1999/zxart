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

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['imageUrls']) {
      this.activeUrl = this.imageUrls[0] ?? '';
    }
  }

  setActive(url: string): void {
    this.activeUrl = url;
  }

  reset(): void {
    this.activeUrl = this.imageUrls[0] ?? '';
  }
}
