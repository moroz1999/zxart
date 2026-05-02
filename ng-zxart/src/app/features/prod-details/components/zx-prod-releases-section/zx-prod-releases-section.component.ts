import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxHeading2Directive,} from '../../../../shared/directives/typography/typography.directives';
import {ProdReleasesApiService} from '../../services/prod-releases-api.service';
import {ProdReleaseDto} from '../../models/prod-release.dto';
import {ZxProdReleaseRowComponent} from '../zx-prod-release-row/zx-prod-release-row.component';

@Component({
  selector: 'zx-prod-releases-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxSkeletonComponent,
    ZxTableComponent,
    ZxHeading2Directive,
    ZxProdReleaseRowComponent,
  ],
  templateUrl: './zx-prod-releases-section.component.html',
  styleUrls: ['./zx-prod-releases-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleasesSectionComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) prodUrl!: string;

  loading = false;
  loaded = false;
  releases: ProdReleaseDto[] = [];

  constructor(
    private readonly api: ProdReleasesApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.api.getReleases(this.elementId).subscribe(releases => {
      this.releases = releases;
      this.loaded = true;
      this.loading = false;
      this.cdr.markForCheck();
    });
  }

  trackById(_index: number, release: ProdReleaseDto): number {
    return release.id;
  }
}
