import {ChangeDetectionStrategy, Component} from '@angular/core';
import {
  ZxFeedbackFormComponent
} from '../../features/feedback/components/zx-feedback-form/zx-feedback-form.component';

/** Routed feedback entrypoint (`/feedback`); the backend resolves the feedback form by type. */
@Component({
  selector: 'zx-feedback-page',
  standalone: true,
  imports: [ZxFeedbackFormComponent],
  template: '<zx-feedback-form></zx-feedback-form>',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FeedbackPageComponent {}
