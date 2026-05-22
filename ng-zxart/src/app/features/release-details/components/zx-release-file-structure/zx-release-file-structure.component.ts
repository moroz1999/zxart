import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ReleaseFileStructureItemDto} from '../../models/release-details.dto';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-release-file-structure',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeadingDirective, TextDirective, ZxInlineComponent, SvgIconComponent],
  templateUrl: './zx-release-file-structure.component.html',
  styleUrl: './zx-release-file-structure.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseFileStructureComponent implements OnInit {
  @Input({required: true}) items!: ReleaseFileStructureItemDto[];

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}folder.svg`, 'folder')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}file.svg`, 'file')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}zip.svg`, 'zip')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}eye.svg`, 'eye')?.subscribe();
  }

  getIcon(item: ReleaseFileStructureItemDto): string {
    if (item.type === 'folder') return 'folder';
    if (item.type === 'zip') return 'zip';
    return 'file';
  }

  countItems(items: ReleaseFileStructureItemDto[]): number {
    return items.reduce((acc, item) => acc + 1 + (item.items?.length ? this.countItems(item.items) : 0), 0);
  }

  range(n: number): number[] {
    return Array.from({length: n});
  }
}
