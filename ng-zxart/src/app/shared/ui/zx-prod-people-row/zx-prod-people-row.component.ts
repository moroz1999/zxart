import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxCreditGroup, ZxCreditsRowComponent} from '../zx-credits-row/zx-credits-row.component';
import {ProdAuthorInfoDto, ProdGroupRefDto} from '../../../features/prod-details/models/prod-core.dto';

const PRIORITY_AUTHOR_ROLES = [
  'role_music',
  'role_intro_music',
  'role_graphics',
  'role_intro_graphics',
  'role_code',
  'role_intro_code',
];

/**
 * Credits of a prod or release: authors grouped by role, then publishers and
 * groups. Maps the prod DTOs onto `zx-credits-row` groups; the party appearance
 * belongs to the hero provenance callout, not here.
 */
@Component({
  selector: 'zx-prod-people-row',
  standalone: true,
  imports: [ZxCreditsRowComponent],
  templateUrl: './zx-prod-people-row.component.html',
  styleUrl: './zx-prod-people-row.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdPeopleRowComponent {
  @Input({required: true}) authors: ProdAuthorInfoDto[] = [];
  @Input({required: true}) publishers: ProdGroupRefDto[] = [];
  @Input() groups: ProdGroupRefDto[] = [];

  get creditGroups(): ZxCreditGroup[] {
    const groups: ZxCreditGroup[] = this.authorRoleGroups.map(group => ({
      labelKey: this.roleLabelKey(group.role),
      people: group.authors.map(author => ({title: author.title, url: author.url})),
    }));

    groups.push({
      labelKey: 'prod-details.publishers',
      people: this.publishers.map(publisher => ({title: publisher.title, url: publisher.url})),
    });

    groups.push({
      labelKey: 'prod-details.groups',
      people: this.groups.map(group => ({title: group.title, url: group.url})),
    });

    return groups;
  }

  private get authorRoleGroups(): {role: string | null; authors: ProdAuthorInfoDto[]}[] {
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
    const groups: {role: string | null; authors: ProdAuthorInfoDto[]}[] = sortedRoles.map(role => ({
      role,
      authors: groupedAuthors.get(role) ?? [],
    }));

    if (authorsWithoutRoles.length > 0) {
      groups.push({role: null, authors: authorsWithoutRoles});
    }

    return groups;
  }

  private roleLabelKey(role: string | null): string {
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
