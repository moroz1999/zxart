import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Observable, Subscription, switchMap} from 'rxjs';
import {map} from 'rxjs/operators';
import {ManageCategoryDto} from '../../features/manage-categories/models/manage-category.dto';
import {ManageCategoriesApiService} from '../../features/manage-categories/services/manage-categories-api.service';
import {
  PRIVILEGE_CATEGORY_DELETE,
  RootPrivilegeService,
} from '../../shared/services/root-privilege.service';
import {ZxManageTabsComponent} from '../../features/manage/components/zx-manage-tabs/zx-manage-tabs.component';
import {ConfirmDialogService} from '../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxTreeComponent, ZxTreeNode} from '../../shared/ui/zx-tree/zx-tree.component';

/** A category row: the tree's own fields plus what the action template shows. */
interface CategoryTreeNode extends ZxTreeNode {
  prods: number;
  prodsTotal: number;
  children: CategoryTreeNode[];
}

/**
 * The production category tree (`/manage/categories`).
 *
 * Every category can take a new subcategory, be renamed or be removed; a
 * top-level one is added from the page header. Deletion runs from the row that
 * owns the category, behind a confirmation — the backend refuses it while the
 * category still holds productions or subcategories.
 *
 * A row prints two counts: what is filed on the category itself, and what its
 * whole branch holds. A section carries few productions of its own and all of
 * its genres', so either number alone misreads the tree.
 */
@Component({
  selector: 'zx-manage-categories-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxButtonComponent,
    ZxFormMessageComponent,
    ZxManageTabsComponent,
    ZxPageLayoutComponent,
    ZxPanelComponent,
    ZxTreeComponent,
    HeadingDirective,
    TextDirective,
  ],
  templateUrl: './manage-categories-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageCategoriesPageComponent implements OnDestroy {
  readonly nodes$: Observable<CategoryTreeNode[]> = this.api.tree$.pipe(
    map(tree => this.buildTree(tree.categories)),
  );

  /** Saving and removing are separate privileges, so the row asks for its own. */
  readonly mayDelete$ = this.rootPrivilegeService.has(PRIVILEGE_CATEGORY_DELETE);

  errorMessage = '';

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: ManageCategoriesApiService,
    private readonly rootPrivilegeService: RootPrivilegeService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onDelete(node: CategoryTreeNode): void {
    const keys = [
      'manage-categories.delete-confirm-title',
      'manage-categories.delete-confirm-message',
      'manage-categories.delete',
      'form.cancel',
    ];
    this.subscriptions.add(
      this.translate.get(keys, {title: node.label}).pipe(
        switchMap((texts: Record<string, string>) => this.confirmDialog.confirm({
          title: texts['manage-categories.delete-confirm-title'],
          message: texts['manage-categories.delete-confirm-message'],
          confirmLabel: texts['manage-categories.delete'],
          cancelLabel: texts['form.cancel'],
          danger: true,
        })),
      ).subscribe(confirmed => {
        if (confirmed) {
          this.runDelete(node.id);
        }
      }),
    );
  }

  private runDelete(id: number): void {
    this.errorMessage = '';
    this.subscriptions.add(
      this.api.delete(id).subscribe({
        error: (error: HttpErrorResponse) => {
          this.errorMessage = error.error?.errorMessage ?? 'manage-categories.error-delete';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  /**
   * The backend sends the tree depth-first with a `parentId`; nesting it here
   * keeps the payload a plain list and costs one pass.
   */
  private buildTree(categories: ManageCategoryDto[]): CategoryTreeNode[] {
    const byId = new Map<number, CategoryTreeNode>();
    for (const category of categories) {
      byId.set(category.id, {
        id: category.id,
        label: category.title,
        prods: category.prods,
        prodsTotal: category.prodsTotal,
        children: [],
      });
    }

    const roots: CategoryTreeNode[] = [];
    for (const category of categories) {
      const node = byId.get(category.id);
      if (!node) {
        continue;
      }
      const parent = category.parentId === null ? null : byId.get(category.parentId) ?? null;
      if (parent === null) {
        roots.push(node);
        continue;
      }
      parent.children.push(node);
    }

    return roots;
  }
}
