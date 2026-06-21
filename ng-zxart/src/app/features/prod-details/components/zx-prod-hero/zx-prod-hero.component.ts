import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ProdAuthorInfoDto, ProdCategoryRefDto, ProdCoreDto} from '../../models/prod-core.dto';
import {ZxProdVoteRowComponent} from '../zx-prod-vote-row/zx-prod-vote-row.component';
import {ZxProdExternalLinksComponent} from '../zx-prod-external-links/zx-prod-external-links.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxProdEditingControlsComponent} from '../zx-prod-editing-controls/zx-prod-editing-controls.component';
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxPartyPlaceComponent} from '../../../../shared/lib/zx-party-place/zx-party-place.component';
import {ZxAddedByComponent} from '../../../../shared/ui/zx-added-by/zx-added-by.component';
import {ZxRatingStripComponent} from '../../../../shared/components/zx-rating-strip/zx-rating-strip.component';

interface ProdAuthorRoleGroup {
  role: string | null;
  authors: ProdAuthorInfoDto[];
}

const PRIORITY_AUTHOR_ROLES = [
  'role_music',
  'role_intro_music',
  'role_graphics',
  'role_intro_graphics',
  'role_code',
  'role_intro_code',
];

@Component({
  selector: 'zx-prod-hero',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdVoteRowComponent,
    ZxProdExternalLinksComponent,
    HeadingDirective,
    TextDirective,
    ZxStackComponent,
    ZxInlineComponent,
    ZxProdEditingControlsComponent,
    ZxChipComponent,
    ZxPartyPlaceComponent,
    ZxAddedByComponent,
    ZxRatingStripComponent,
  ],
  templateUrl: './zx-prod-hero.component.html',
  styleUrls: ['./zx-prod-hero.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdHeroComponent {
  @Input({required: true}) core!: ProdCoreDto;

  get externalLinkLabelKey(): string {
    if (this.core.legalStatus === 'insales') {
      return 'prod-details.purchase';
    }
    if (this.core.legalStatus === 'donationware') {
      return 'prod-details.donate';
    }
    return 'prod-details.open_externallink';
  }

  get leafCategories(): ProdCategoryRefDto[] {
    return this.core.categoriesPaths
      .map(path => path.categories[path.categories.length - 1])
      .filter((cat): cat is ProdCategoryRefDto => !!cat);
  }

  get showLegalStatus(): boolean {
    return this.core.legalStatus !== 'unknown';
  }

  get authorRoleGroups(): ProdAuthorRoleGroup[] {
    const groupedAuthors = new Map<string, ProdAuthorInfoDto[]>();
    const authorsWithoutRoles: ProdAuthorInfoDto[] = [];

    for (const author of this.core.authors) {
      const roles = author.roles.length ? author.roles : [null];
      for (const role of roles) {
        if (role === null || role === 'unknown') {
          authorsWithoutRoles.push(author);
          continue;
        }
        groupedAuthors.set(role, [...(groupedAuthors.get(role) ?? []), author]);
      }
    }

    const sortedRoles = Array.from(groupedAuthors.keys()).sort((a, b) => this.getRoleOrder(a) - this.getRoleOrder(b));
    const groups: ProdAuthorRoleGroup[] = sortedRoles.map(role => ({role, authors: groupedAuthors.get(role) ?? []}));

    if (authorsWithoutRoles.length > 0) {
      groups.push({role: null, authors: authorsWithoutRoles});
    }

    return groups;
  }

  get hasAuthorRoleGroups(): boolean {
    return this.authorRoleGroups.length > 0;
  }

  get hasPeopleInfo(): boolean {
    return this.hasAuthorRoleGroups || this.core.publishers.length > 0 || this.core.groups.length > 0 || this.core.party !== null;
  }

  trackAuthorRoleGroup(_index: number, group: ProdAuthorRoleGroup): string {
    return group.role ?? 'authors';
  }

  trackAuthor(_index: number, author: ProdAuthorInfoDto): number {
    return author.id;
  }

  roleLabelKey(role: string | null): string {
    if (role === null || role === 'unknown') {
      return 'prod-details.authors';
    }
    return `author.role.${role.replace(/^role_/, '')}`;
  }

  private getRoleOrder(role: string): number {
    const priorityIndex = PRIORITY_AUTHOR_ROLES.indexOf(role);
    return priorityIndex === -1 ? PRIORITY_AUTHOR_ROLES.length : priorityIndex;
  }
}
