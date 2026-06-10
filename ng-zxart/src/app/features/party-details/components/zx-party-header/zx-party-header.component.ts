import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {PartyCoreDto} from '../../models/party-core.dto';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {environment} from '../../../../../environments/environment';
import {ZxPartyEditingControlsComponent} from '../zx-party-editing-controls/zx-party-editing-controls.component';

@Component({
  selector: 'zx-party-header',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxInlineComponent,
    ZxButtonComponent,
    ZxPartyEditingControlsComponent,
    SvgIconComponent,
    TextDirective,
    HeadingDirective,
  ],
  templateUrl: './zx-party-header.component.html',
  styleUrl: './zx-party-header.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyHeaderComponent implements OnInit {
  @Input() core!: PartyCoreDto;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}location.svg`, 'location')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
  }
}
