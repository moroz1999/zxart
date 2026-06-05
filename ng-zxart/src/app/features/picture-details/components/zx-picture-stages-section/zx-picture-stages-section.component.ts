import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  Input,
  NgZone,
  OnDestroy,
  OnInit,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {Subscription} from 'rxjs';
import {environment} from '../../../../../environments/environment';
import {GifFrame, GifFramesService} from '../../services/gif-frames.service';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxInputRangeComponent} from '../../../../shared/ui/zx-input-range/zx-input-range.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';

@Component({
  selector: 'zx-picture-stages-section',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxStackComponent,
    ZxInlineComponent,
    ZxButtonComponent,
    ZxInputRangeComponent,
    ZxGridComponent,
    TextDirective,
    HeadingDirective,
    SvgIconComponent,
  ],
  templateUrl: './zx-picture-stages-section.component.html',
  styleUrls: ['./zx-picture-stages-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureStagesSectionComponent implements OnInit, OnDestroy {
  @Input({required: true}) sequenceUrl!: string;

  frames: GifFrame[] = [];
  milestones: number[] = [];
  index = 0;
  playing = false;
  loading = true;

  private timerId: ReturnType<typeof setTimeout> | null = null;
  private decodeSubscription?: Subscription;

  constructor(
    private readonly gifFramesService: GifFramesService,
    private readonly zone: NgZone,
    private readonly cdr: ChangeDetectorRef,
    private readonly iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}play.svg`, 'play')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}pause.svg`, 'pause')?.subscribe();

    this.decodeSubscription = this.gifFramesService.decode(this.sequenceUrl).subscribe(decoded => {
      this.loading = false;
      if (decoded && decoded.frames.length) {
        this.frames = decoded.frames;
        this.index = this.frames.length - 1;
        this.milestones = this.computeMilestones(this.frames.length);
      }
      this.cdr.markForCheck();
    });
  }

  ngOnDestroy(): void {
    this.stopTimer();
    this.decodeSubscription?.unsubscribe();
  }

  get frameCount(): number {
    return this.frames.length;
  }

  get currentFrame(): GifFrame | null {
    return this.frames[this.index] ?? null;
  }

  togglePlay(): void {
    this.playing = !this.playing;
    if (this.playing) {
      this.scheduleNext();
    } else {
      this.stopTimer();
    }
  }

  onScrub(value: number): void {
    this.stopPlayback();
    this.index = value;
  }

  jumpTo(frameIndex: number): void {
    this.stopPlayback();
    this.index = frameIndex;
  }

  private scheduleNext(): void {
    this.stopTimer();
    // Hold the last frame for 2s, then loop back to the start.
    const isLast = this.index === this.frames.length - 1;
    const delay = isLast ? 2000 : this.currentFrame?.delay || 380;
    this.zone.runOutsideAngular(() => {
      this.timerId = setTimeout(() => {
        this.zone.run(() => {
          this.index = (this.index + 1) % this.frames.length;
          this.cdr.markForCheck();
          if (this.playing) {
            this.scheduleNext();
          }
        });
      }, delay);
    });
  }

  private stopPlayback(): void {
    this.playing = false;
    this.stopTimer();
  }

  private stopTimer(): void {
    if (this.timerId !== null) {
      clearTimeout(this.timerId);
      this.timerId = null;
    }
  }

  private computeMilestones(count: number): number[] {
    const want = Math.min(5, count);
    const set = new Set<number>();
    for (let k = 0; k < want; k++) {
      set.add(Math.round((k * (count - 1)) / (want - 1)));
    }
    return [...set].sort((a, b) => a - b);
  }
}
