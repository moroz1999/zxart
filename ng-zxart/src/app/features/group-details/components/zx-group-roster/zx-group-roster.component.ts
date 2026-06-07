import {ChangeDetectionStrategy, Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {Observable, of} from 'rxjs';
import {GroupMemberDto, GroupSubgroupDto} from '../../models/group-core.dto';
import {GroupRosterApiService, GroupRosterDto} from '../../services/group-roster-api.service';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxChipComponent, ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxSkeletonBoneComponent} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {environment} from '../../../../../environments/environment';

const ROLE_ORDER = ['coder', 'graphician', 'musician', 'support', 'cracker', 'organizer', 'hardware', 'tester', 'gamedesigner'];
const SKELETON_MEMBER_ITEMS = [0, 1, 2, 3, 4, 5];
const SKELETON_CHIP_ITEMS = [0, 1, 2];

@Component({
  selector: 'zx-group-roster',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxPanelComponent,
    ZxChipComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxInlineComponent,
    ZxStackComponent,
    ZxSkeletonBoneComponent,
    TextDirective,
  ],
  templateUrl: './zx-group-roster.component.html',
  styleUrl: './zx-group-roster.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupRosterComponent implements OnChanges, OnInit {
  @Input() elementId = 0;

  activeSubgroup = 'all';
  roleFilter = 'all';
  roster$: Observable<GroupRosterDto> = of({subgroups: [], members: []});
  readonly skeletonMemberItems = SKELETON_MEMBER_ITEMS;
  readonly skeletonChipItems = SKELETON_CHIP_ITEMS;

  constructor(
    private readonly iconReg: SvgIconRegistryService,
    private readonly rosterApiService: GroupRosterApiService,
  ) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['elementId'] && this.elementId > 0) {
      this.roster$ = this.rosterApiService.getRoster(this.elementId);
    }
  }

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
  }

  filterableSubgroups(roster: GroupRosterDto): GroupSubgroupDto[] {
    return roster.subgroups.filter(subgroup => this.membersInSubgroup(roster, subgroup.title) > 0);
  }

  presentRoles(roster: GroupRosterDto): string[] {
    const counts = this.roleCounts(roster);
    return ROLE_ORDER.filter(role => (counts[role] ?? 0) > 0);
  }

  roleCounts(roster: GroupRosterDto): Record<string, number> {
    const counts: Record<string, number> = {};
    for (const member of roster.members) {
      for (const role of member.roles) {
        counts[role] = (counts[role] ?? 0) + 1;
      }
    }
    return counts;
  }

  filteredMembers(roster: GroupRosterDto): GroupMemberDto[] {
    return roster.members.filter(member =>
      (this.activeSubgroup === 'all' || member.subgroups.includes(this.activeSubgroup))
      && (this.roleFilter === 'all' || member.roles.includes(this.roleFilter)),
    );
  }

  membersInSubgroup(roster: GroupRosterDto, subgroupTitle: string): number {
    return roster.members.filter(member => member.subgroups.includes(subgroupTitle)).length;
  }

  setSubgroup(subgroupTitle: string): void {
    this.activeSubgroup = this.activeSubgroup === subgroupTitle ? 'all' : subgroupTitle;
  }

  setRole(role: string): void {
    this.roleFilter = this.roleFilter === role ? 'all' : role;
  }

  roleChipColor(role: string): ZxChipColor {
    if (role === 'musician') return 'primary';
    if (role === 'coder') return 'code';
    if (role === 'graphician') return 'artist';
    return 'intro';
  }

  roleLabelKey(role: string): string {
    return `group-details.roles.${role}`;
  }
}
