import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
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
 * Author claim confirmation page (`author/:id/claim`). Sends a claim request
 * (admins get an approval email) and shows the result; no navigation.
 */
@Component({
  selector: 'zx-claim-page',
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
  templateUrl: './claim-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ClaimPageComponent implements OnInit, OnDestroy {
  submitting = false;
  done = false;
  succeeded = false;
  errorMessage = '';

  private elementId = 0;
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly route: ActivatedRoute,
    private readonly cdr: ChangeDetectorRef,
    private readonly formSave: FormSaveApiService,
  ) {}

  ngOnInit(): void {
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onConfirm(): void {
    this.submitting = true;
    this.errorMessage = '';
    this.subscriptions.add(
      this.formSave.save(this.elementId, {fields: {}}, 'claim').subscribe({
        next: result => {
          this.submitting = false;
          this.done = true;
          this.succeeded = (result as {success?: boolean}).success === true;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'claim.error';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
