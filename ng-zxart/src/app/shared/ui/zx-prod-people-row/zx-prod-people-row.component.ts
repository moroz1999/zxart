import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxInlineComponent} from '../zx-inline/zx-inline.component';
import {TextDirective} from '../typography/directives/text.directive';
import {ProdAuthorInfoDto, ProdGroupRefDto, ProdPartyInfoDto} from '../../../features/prod-details/models/prod-core.dto';
import {ZxPartyPlaceComponent} from '../../lib/zx-party-place/zx-party-place.component';

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
  selector: 'zx-prod-people-row',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxInlineComponent, TextDirective, ZxPartyPlaceComponent],
  templateUrl: './zx-prod-people-row.component.html',
  styleUrl: './zx-prod-people-row.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdPeopleRowComponent {
  @Input({required: true}) authors: ProdAuthorInfoDto[] = [];
  @Input({required: true}) publishers: ProdGroupRefDto[] = [];
  @Input() groups: ProdGroupRefDto[] = [];
  @Input() party: ProdPartyInfoDto | null = null;

  get authorRoleGroups(): ProdAuthorRoleGroup[] {
    const groupedAuthors = new Map<string, ProdAuthorInfoDto[]>();
    const authorsWithoutRoles: ProdAuthorInfoDto[] = [];

    for (const author of this.authors) {
      const roles = author.roles.length ? author.roles : [null];
      for (const role of roles) {
        if (role === null || role === 'unknown') {
          authorsWithoutRoles.push(author);
          continue;
        }
        groupedAuthors.set(role, [...(groupedAuthors.get(role) ?? []), author]);
      }
    }

    const sortedRoles = Array.from(groupedAuthors.keys()).sort(
      (a, b) => this.getRoleOrder(a) - this.getRoleOrder(b),
    );
    const groups: ProdAuthorRoleGroup[] = sortedRoles.map(role => ({
      role,
      authors: groupedAuthors.get(role) ?? [],
    }));

    if (authorsWithoutRoles.length > 0) {
      groups.push({role: null, authors: authorsWithoutRoles});
    }

    return groups;
  }

  get hasAuthorRoleGroups(): boolean {
    return this.authorRoleGroups.length > 0;
  }

  get hasAnyPeople(): boolean {
    return this.hasAuthorRoleGroups || this.publishers.length > 0 || this.groups.length > 0 || this.party !== null;
  }

  trackRoleGroup(_index: number, group: ProdAuthorRoleGroup): string {
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
    const idx = PRIORITY_AUTHOR_ROLES.indexOf(role);
    return idx === -1 ? PRIORITY_AUTHOR_ROLES.length : idx;
  }
}
