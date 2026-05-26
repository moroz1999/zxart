import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CollaboratorPersonDto} from '../../services/author-collaborators-api.service';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-author-person-card',
  standalone: true,
  imports: [CommonModule, TranslateModule, SvgIconComponent, TextDirective, HeadingDirective],
  templateUrl: './zx-author-person-card.component.html',
  styleUrl: './zx-author-person-card.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorPersonCardComponent implements OnInit {
  @Input() person!: CollaboratorPersonDto;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}image.svg`, 'image')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}music-note.svg`, 'music-note')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}gamepad.svg`, 'gamepad')?.subscribe();
  }
}
