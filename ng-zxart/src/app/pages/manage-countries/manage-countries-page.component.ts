import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Observable, Subscription, switchMap} from 'rxjs';
import {map} from 'rxjs/operators';
import {ManagePlacesDto} from '../../features/manage-countries/models/manage-place.dto';
import {ManageCountriesApiService} from '../../features/manage-countries/services/manage-countries-api.service';
import {
  PRIVILEGE_CITY_DELETE,
  PRIVILEGE_COUNTRY_DELETE,
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

/** A country or city row: the tree's own fields plus what the action template shows. */
interface PlaceTreeNode extends ZxTreeNode {
  usages: number;
  children: PlaceTreeNode[];
}

/**
 * The countries of the geo section and their cities (`/manage/countries`).
 *
 * The list is the same collapsible tree the categories use, two levels deep: a
 * country expands into its cities. Deletion runs from the row that owns the
 * place, behind a confirmation — the backend refuses it while the country still
 * has cities, or while an author, a group or a party still names the place.
 */
@Component({
  selector: 'zx-manage-countries-page',
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
  templateUrl: './manage-countries-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageCountriesPageComponent implements OnDestroy {
  readonly nodes$: Observable<PlaceTreeNode[]> = this.api.places$.pipe(
    map(places => this.buildTree(places)),
  );

  /**
   * Saving and removing are separate privileges, and a country and a city are
   * separate element types, so each row asks for the one it needs.
   */
  readonly mayDeleteCountry$ = this.rootPrivilegeService.has(PRIVILEGE_COUNTRY_DELETE);
  readonly mayDeleteCity$ = this.rootPrivilegeService.has(PRIVILEGE_CITY_DELETE);

  errorMessage = '';

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: ManageCountriesApiService,
    private readonly rootPrivilegeService: RootPrivilegeService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onDelete(node: PlaceTreeNode, level: number): void {
    const isCity = level > 0;
    const keys = [
      isCity ? 'manage-countries.delete-city-confirm-title' : 'manage-countries.delete-confirm-title',
      isCity ? 'manage-countries.delete-city-confirm-message' : 'manage-countries.delete-confirm-message',
      'manage-countries.delete',
      'form.cancel',
    ];
    this.subscriptions.add(
      this.translate.get(keys, {title: node.label}).pipe(
        switchMap((texts: Record<string, string>) => this.confirmDialog.confirm({
          title: texts[keys[0]],
          message: texts[keys[1]],
          confirmLabel: texts['manage-countries.delete'],
          cancelLabel: texts['form.cancel'],
          danger: true,
        })),
      ).subscribe(confirmed => {
        if (confirmed) {
          this.runDelete(node.id, isCity);
        }
      }),
    );
  }

  private runDelete(id: number, isCity: boolean): void {
    this.errorMessage = '';
    const delete$ = isCity ? this.api.deleteCity(id) : this.api.deleteCountry(id);
    this.subscriptions.add(
      delete$.subscribe({
        error: (error: HttpErrorResponse) => {
          this.errorMessage = error.error?.errorMessage ?? 'manage-countries.error-delete';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  /** Countries and cities arrive as two flat lists; the tree is nested here. */
  private buildTree(places: ManagePlacesDto): PlaceTreeNode[] {
    const byCountry = new Map<number, PlaceTreeNode>();
    const roots: PlaceTreeNode[] = [];

    for (const country of places.countries) {
      const node: PlaceTreeNode = {
        id: country.id,
        label: country.title,
        usages: country.usages,
        children: [],
      };
      byCountry.set(country.id, node);
      roots.push(node);
    }

    for (const city of places.cities) {
      byCountry.get(city.countryId)?.children.push({
        id: city.id,
        label: city.title,
        usages: city.usages,
        children: [],
      });
    }

    return roots;
  }
}
