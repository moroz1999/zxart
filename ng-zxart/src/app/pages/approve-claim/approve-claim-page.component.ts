import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute, RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ClaimApproval} from '../../features/approve-claim/models/claim-approval';
import {ClaimApprovalApiService} from '../../features/approve-claim/services/claim-approval-api.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxMetaRowComponent} from '../../shared/ui/zx-meta-row/zx-meta-row.component';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/**
 * Routed page for `approve-claim` — the target of the moderation mail sent when
 * an account claims an author. The mail is forwardable, so the page states the
 * claim to whoever opens it but offers the button only to a visitor holding the
 * privilege; the backend enforces the same on the approval itself.
 */
@Component({
  selector: 'zx-approve-claim-page',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    TranslateModule,
    ZxButtonComponent,
    ZxFormMessageComponent,
    ZxMetaRowComponent,
    ZxPanelComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './approve-claim-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ApproveClaimPageComponent implements OnInit, OnDestroy {
  pending = true;
  approving = false;
  claim: ClaimApproval | null = null;
  /** Translation key of the load or approval failure, empty while nothing failed. */
  error = '';

  private authorId = 0;
  private userId = 0;
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly route: ActivatedRoute,
    private readonly api: ClaimApprovalApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    const params = this.route.snapshot.queryParamMap;
    this.authorId = Number(params.get('authorId') ?? 0);
    this.userId = Number(params.get('userId') ?? 0);
    if (this.authorId <= 0 || this.userId <= 0) {
      this.pending = false;
      this.error = 'approve-claim.error-invalid';
      return;
    }

    this.subscriptions.add(
      this.api.getClaim(this.authorId, this.userId).subscribe({
        next: claim => {
          this.pending = false;
          this.claim = claim;
          this.cdr.markForCheck();
        },
        error: () => {
          this.pending = false;
          this.error = 'approve-claim.error-not-found';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  approve(): void {
    if (this.approving) {
      return;
    }
    this.approving = true;
    this.error = '';

    this.subscriptions.add(
      this.api.approve(this.authorId, this.userId).subscribe({
        next: claim => {
          this.approving = false;
          this.claim = claim;
          this.cdr.markForCheck();
        },
        error: () => {
          this.approving = false;
          this.error = 'approve-claim.error-failed';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }
}
