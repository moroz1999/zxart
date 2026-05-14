import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  HostBinding,
  Input,
  OnDestroy,
  OnInit,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {HeadingDirective, TextDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxMusicListComponent} from '../../../music-list/components/zx-music-list/zx-music-list.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {
  ZxTuneTableSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-tune-table-skeleton/zx-tune-table-skeleton.component';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {MusicListService} from '../../../music-list/services/music-list.service';
import {Subscription} from 'rxjs';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-prod-music-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    HeadingDirective,
    ZxMusicListComponent,
    ZxTableComponent,
    ZxTuneTableSkeletonComponent,
    TextDirective,
    ZxStackComponent,
  ],
  templateUrl: './zx-prod-music-section.component.html',
  styleUrls: ['./zx-prod-music-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdMusicSectionComponent implements OnInit, OnDestroy {
  @Input({required: true}) elementId!: number;

  loading = true;
  error = false;
  tunes: ZxTuneDto[] = [];

  private readonly subscription = new Subscription();

  @HostBinding('style.display')
  get display(): string {
    return !this.loading && !this.error && this.tunes.length === 0 ? 'none' : 'block';
  }

  constructor(
    private readonly musicListService: MusicListService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.subscription.add(
      this.musicListService.getTunes(this.elementId).subscribe({
        next: tunes => {
          this.loading = false;
          this.tunes = tunes;
          this.cdr.markForCheck();
        },
        error: () => {
          this.loading = false;
          this.error = true;
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }
}
