import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {GroupCoreDto, GroupMemberDto, GroupSubgroupDto} from '../../models/group-core.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxChipComponent, ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {environment} from '../../../../../environments/environment';

const ROLE_ORDER = ['coder', 'graphician', 'musician', 'support', 'cracker', 'organizer', 'hardware', 'tester', 'gamedesigner'];

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
    TextDirective,
  ],
  templateUrl: './zx-group-roster.component.html',
  styleUrl: './zx-group-roster.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupRosterComponent implements OnInit {
  @Input() core!: GroupCoreDto;

  activeSubgroup = 'all';
  roleFilter = 'all';

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
  }

  get subgroups(): GroupSubgroupDto[] {
    return this.core.subgroups;
  }

  get filterableSubgroups(): GroupSubgroupDto[] {
    return this.subgroups.filter(subgroup => this.membersInSubgroup(subgroup.title) > 0);
  }

  get presentRoles(): string[] {
    const counts = this.roleCounts;
    return ROLE_ORDER.filter(role => (counts[role] ?? 0) > 0);
  }

  get roleCounts(): Record<string, number> {
    const counts: Record<string, number> = {};
    for (const member of this.core.members) {
      for (const role of member.roles) {
        counts[role] = (counts[role] ?? 0) + 1;
      }
    }
    return counts;
  }

  get filteredMembers(): GroupMemberDto[] {
    return this.core.members.filter(member =>
      (this.activeSubgroup === 'all' || member.subgroups.includes(this.activeSubgroup))
      && (this.roleFilter === 'all' || member.roles.includes(this.roleFilter)),
    );
  }

  membersInSubgroup(subgroupTitle: string): number {
    return this.core.members.filter(member => member.subgroups.includes(subgroupTitle)).length;
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
