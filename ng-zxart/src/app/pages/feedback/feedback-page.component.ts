import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxFeedbackFormComponent
} from '../../features/feedback/components/zx-feedback-form/zx-feedback-form.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed feedback entrypoint (`/feedback`); the backend resolves the feedback form by type. */
@Component({
  selector: 'zx-feedback-page',
  standalone: true,
  imports: [TranslateModule, ZxFeedbackFormComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './feedback-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FeedbackPageComponent {}
