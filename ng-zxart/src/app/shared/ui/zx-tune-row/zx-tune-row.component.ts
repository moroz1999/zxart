import {
  ChangeDetectionStrategy,
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnDestroy,
  OnInit,
  Output,
  SimpleChanges
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {Subscription} from 'rxjs';
import {ZxTuneDto} from '../../models/zx-tune-dto';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';
import {ZxItemControlsComponent} from '../zx-item-controls/zx-item-controls.component';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {TextDirective} from '../typography/directives/text.directive';
import {environment} from '../../../../environments/environment';
import {ZxPartyPlaceComponent} from '../../lib/zx-party-place/zx-party-place.component';

@Component({
  selector: 'zx-tune-row',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxBadgeComponent,
    ZxItemControlsComponent,
    TextDirective,
    ZxPartyPlaceComponent,
  ],
  templateUrl: './zx-tune-row.component.html',
  styleUrls: ['./zx-tune-row.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTuneRowComponent implements OnInit, OnChanges, OnDestroy {
  @Input() tune!: ZxTuneDto;
  @Input() index?: number;
  @Input() isPlaying = false;
  @Output() playRequested = new EventEmitter<ZxTuneDto>();
  @Output() pauseRequested = new EventEmitter<void>();

  realtimeBadgeLabel = '';

  private labelSub: Subscription | null = null;

  constructor(
    private translateService: TranslateService,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}play.svg`, 'play')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}pause.svg`, 'pause')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}music-note.svg`, 'music-note')?.subscribe();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['tune']) {
      this.updateRealtimeBadgeLabel();
    }
  }

  ngOnDestroy(): void {
    this.labelSub?.unsubscribe();
  }

  requestPlay(): void {
    if (this.isPlaying) {
      this.pauseRequested.emit();
      return;
    }
    if (!this.tune.isPlayable || !this.tune.mp3Url) {
      return;
    }
    this.playRequested.emit(this.tune);
  }

  private updateRealtimeBadgeLabel(): void {
    this.labelSub?.unsubscribe();
    const key = this.tune?.compo ? `tune.compo.${this.tune.compo}` : 'firstpage.realtime';
    this.labelSub = this.translateService.stream(key).subscribe(translated => {
      this.realtimeBadgeLabel = this.tune?.compo && translated === key
        ? this.tune.compo
        : translated;
    });
  }
}
