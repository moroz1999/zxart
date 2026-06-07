import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {GroupCoreDto} from '../../models/group-core.dto';
import {ZxChipComponent, ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxGroupEditingControlsComponent} from '../zx-group-editing-controls/zx-group-editing-controls.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-group-header',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxChipComponent,
    ZxInlineComponent,
    ZxGroupEditingControlsComponent,
    SvgIconComponent,
    TextDirective,
    HeadingDirective,
  ],
  templateUrl: './zx-group-header.component.html',
  styleUrl: './zx-group-header.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupHeaderComponent implements OnInit {
  @Input() core!: GroupCoreDto;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}location.svg`, 'location')?.subscribe();
  }

  natureColor(nature: string): ZxChipColor {
    if (nature === 'developer') return 'primary';
    if (nature === 'publisher') return 'artist';
    return 'code';
  }
}
