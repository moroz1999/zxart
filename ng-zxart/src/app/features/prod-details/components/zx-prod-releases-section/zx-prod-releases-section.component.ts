import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {forkJoin, Subscription} from 'rxjs';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxRowSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-row-skeleton/zx-row-skeleton.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxHeading2Directive,} from '../../../../shared/directives/typography/typography.directives';
import {ProdReleasesApiService} from '../../services/prod-releases-api.service';
import {ProdReleaseDto} from '../../models/prod-release.dto';
import {ZxProdReleaseRowComponent} from '../zx-prod-release-row/zx-prod-release-row.component';
import {ElementPrivilegesApiService} from '../../../../shared/services/element-privileges-api.service';

@Component({
  selector: 'zx-prod-releases-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxRowSkeletonComponent,
    ZxTableComponent,
    ZxHeading2Directive,
    ZxProdReleaseRowComponent,
  ],
  templateUrl: './zx-prod-releases-section.component.html',
  styleUrls: ['./zx-prod-releases-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleasesSectionComponent implements OnDestroy {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) prodUrl!: string;

  loading = false;
  loaded = false;
  releases: ProdReleaseDto[] = [];
  canUploadScreenshot = false;

  @HostBinding('style.display')
  get display(): string {
    return this.loaded && this.releases.length === 0 ? 'none' : '';
  }

  private readonly subscription = new Subscription();

  constructor(
    private readonly api: ProdReleasesApiService,
    private readonly elementPrivilegesApi: ElementPrivilegesApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.subscription.add(
      forkJoin({
        releases: this.api.getReleases(this.elementId),
        privileges: this.elementPrivilegesApi.getPrivileges(this.elementId, ['uploadScreenshot']),
      }).subscribe(({releases, privileges}) => {
        this.releases = releases;
        this.canUploadScreenshot = privileges.uploadScreenshot === true;
        this.loaded = true;
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  trackById(_index: number, release: ProdReleaseDto): number {
    return release.id;
  }

  get screenshotUploadUrl(): string {
    return `${this.prodUrl}id:${this.elementId}/action:uploadScreenshot/`;
  }
}
