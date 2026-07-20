import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/**
 * Generic convert confirmation page (`<entity>/:id/convert-to-*`). Runs a legacy
 * convert action (no form fields) and navigates to the newly created entity.
 * Action, target path and prompt come from the route data.
 */
@Component({
  selector: 'zx-convert-form-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxButtonComponent,
    ZxFormActionsComponent,
    ZxFormMessageComponent,
    ZxStackComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './convert-form-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ConvertFormPageComponent implements OnInit, OnDestroy {
  messageKey = '';
  submitting = false;
  errorMessage = '';

  private elementId = 0;
  private action = '';
  private targetPath = '';
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly formSave: FormSaveApiService,
  ) {}

  ngOnInit(): void {
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
    this.action = this.route.snapshot.data['action'] ?? '';
    this.targetPath = this.route.snapshot.data['targetPath'] ?? '';
    this.messageKey = this.route.snapshot.data['messageKey'] ?? '';
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onConfirm(): void {
    this.submitting = true;
    this.errorMessage = '';
    this.subscriptions.add(
      this.formSave.save(this.elementId, {fields: {}}, this.action).subscribe({
        next: result => this.router.navigateByUrl(`/${this.targetPath}/${result.id}`),
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'convert-form.error';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
