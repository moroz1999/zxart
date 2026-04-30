import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';
import {
  ZxBodyDirective,
  ZxBodySmMutedDirective,
} from '../../directives/typography/typography.directives';

export interface ZxProdCardData {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly year: number;
  readonly legalStatus: string;
  readonly legalStatusLabel: string;
  readonly votes: number;
  readonly votesAmount: number;
  readonly imageUrl: string | null;
}

@Component({
  selector: 'zx-prod-card',
  standalone: true,
  imports: [
    CommonModule,
    ZxPanelComponent,
    ZxBadgeComponent,
    ZxBodyDirective,
    ZxBodySmMutedDirective,
  ],
  templateUrl: './zx-prod-card.component.html',
  styleUrl: './zx-prod-card.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdCardComponent {
  @Input({required: true}) data!: ZxProdCardData;

  get showLegalBadge(): boolean {
    const s = this.data.legalStatus;
    return s === 'mia' || s === 'unreleased';
  }
}
