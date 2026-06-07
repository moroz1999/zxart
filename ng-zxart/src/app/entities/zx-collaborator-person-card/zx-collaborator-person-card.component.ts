import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CollaboratorPersonCardData} from './collaborator-person-card.model';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {environment} from '../../../environments/environment';

@Component({
  selector: 'zx-collaborator-person-card',
  standalone: true,
  imports: [CommonModule, TranslateModule, SvgIconComponent, TextDirective, HeadingDirective],
  templateUrl: './zx-collaborator-person-card.component.html',
  styleUrl: './zx-collaborator-person-card.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCollaboratorPersonCardComponent implements OnInit {
  @Input() person!: CollaboratorPersonCardData;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}image.svg`, 'image')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}music-note.svg`, 'music-note')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}gamepad.svg`, 'gamepad')?.subscribe();
  }

  get hasStats(): boolean {
    return (this.person.jointPictures ?? 0) > 0
      || (this.person.jointTunes ?? 0) > 0
      || (this.person.jointProds ?? 0) > 0;
  }

  roleLabelKey(role: string): string {
    return `prod-details.role_${role}`;
  }
}
