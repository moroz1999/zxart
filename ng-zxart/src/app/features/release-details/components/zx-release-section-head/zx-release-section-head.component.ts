import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';

@Component({
  selector: 'zx-release-section-head',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeadingDirective, TextDirective, ZxInlineComponent],
  templateUrl: './zx-release-section-head.component.html',
  styleUrl: './zx-release-section-head.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseSectionHeadComponent {
  @Input({required: true}) titleKey!: string;
  @Input() count: number | null = null;
}
