import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable} from 'rxjs';
import {HardwareItemDto} from '../../features/manage-hardware/models/hardware-catalog.dto';
import {ManageHardwareApiService} from '../../features/manage-hardware/services/manage-hardware-api.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

interface HardwareCategoryVm {
  code: string;
  items: HardwareItemDto[];
}

interface ManageHardwareVm {
  languages: string[];
  categories: HardwareCategoryVm[];
}

/**
 * The hardware catalog (`/manage/hardware`): every item grouped by category, in
 * the catalog's own display order, with its labels per language and how many
 * works reference it.
 */
@Component({
  selector: 'zx-manage-hardware-page',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslateModule, ZxButtonComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './manage-hardware-page.component.html',
  styleUrls: ['./manage-hardware-page.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageHardwarePageComponent {
  readonly vm$: Observable<ManageHardwareVm> = this.api.catalog$.pipe(
    map(catalog => ({
      languages: catalog.languages,
      // the backend already emits catalog order, so grouping preserves it
      categories: catalog.categories
        .map(code => ({code, items: catalog.items.filter(item => item.category === code)}))
        .filter(category => category.items.length > 0),
    })),
  );

  constructor(private readonly api: ManageHardwareApiService) {}

  nameOf(item: HardwareItemDto, language: string): string {
    return item.names[language]?.name ?? '';
  }

  shortNameOf(item: HardwareItemDto, language: string): string {
    return item.names[language]?.shortName ?? '';
  }

  trackById(index: number, item: HardwareItemDto): number {
    return item.id;
  }
}
