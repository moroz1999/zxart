import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {NgxExtendedPdfViewerModule, pdfDefaultOptions} from 'ngx-extended-pdf-viewer';
import {environment} from '../../../../environments/environment';

/**
 * Wraps the PDF engine so it stays out of the eagerly loaded bundles:
 * hosts must render this component inside a `@defer` block only.
 */
@Component({
  selector: 'zx-pdf-viewer',
  standalone: true,
  imports: [NgxExtendedPdfViewerModule],
  templateUrl: './zx-pdf-viewer.component.html',
  styleUrl: './zx-pdf-viewer.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPdfViewerComponent {
  @Input({required: true}) src!: string;
  @Input() height = '75vh';

  constructor() {
    pdfDefaultOptions.assetsFolder = environment.pdfAssetsFolder;
  }
}
