import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {
  ZxProdFileListItem,
  ZxProdFilesListComponent,
} from '../../../../shared/ui/zx-prod-files-list/zx-prod-files-list.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ProdRzxApiService} from '../../services/prod-rzx-api.service';
import {ProdFileDto} from '../../models/prod-file.dto';

@Component({
  selector: 'zx-prod-rzx-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxSkeletonComponent,
    ZxProdFilesListComponent,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-prod-rzx-section.component.html',
  styleUrls: ['./zx-prod-rzx-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdRzxSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  files: ZxProdFileListItem[] = [];

  constructor(
    private readonly api: ProdRzxApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.api.getRzx(this.elementId).subscribe(files => {
      this.files = files.map(this.toListItem);
      this.loaded = true;
      this.loading = false;
      this.cdr.markForCheck();
    });
  }

  private toListItem(file: ProdFileDto): ZxProdFileListItem {
    return {
      id: file.id,
      title: file.title,
      fileName: file.fileName,
      downloadUrl: file.downloadUrl,
      author: file.author,
    };
  }
}
