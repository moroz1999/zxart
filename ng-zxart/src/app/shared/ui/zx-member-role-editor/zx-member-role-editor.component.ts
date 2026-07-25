import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {EntityRef} from '../../models/entity-ref';
import {ChipItem} from '../../models/chip-item';
import {ZxChipsComponent} from '../zx-chips/zx-chips.component';
import {ZxCloseButtonComponent} from '../zx-close-button/zx-close-button.component';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxEntityAutocompleteComponent} from '../zx-entity-autocomplete/zx-entity-autocomplete.component';
import {TextDirective} from '../typography/directives/text.directive';
import {DropdownPopoverAnimation} from '../../animations/popover-animations';
import {listKeyboardNav} from '../../utils/list-keyboard-nav';
import {MemberFields, MemberRoleItem} from './zx-member-role-editor.models';

interface EditableMember {
  id: number;
  title: string;
  persisted: boolean;
  startDate: string;
  endDate: string;
  roles: Set<string>;
  roleQuery: string;
  roleFocused: boolean;
  roleActiveIndex: number;
}

/**
 * Member / role manager for an entity's authorship (e.g. a group's members).
 * Members are shown as a responsive grid of cards; each card carries the member's
 * selected roles as removable chips plus a searchable role adder. The author
 * picker appends new members to the same card grid, and multiple members can be
 * queued before the form is saved. Emits per-author role and period maps;
 * persisted-member removal is emitted so the host can run the `deleteAuthor`
 * action live (no page reload).
 *
 * Selected roles render as removable {@link ZxChipsComponent} chips; the role
 * options list uses native `<button>` markup — permitted for this atomic
 * design-system control (design-system rule 10).
 */
@Component({
  selector: 'zx-member-role-editor',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxChipsComponent,
    ZxCloseButtonComponent,
    ZxInputComponent,
    ZxEntityAutocompleteComponent,
    TextDirective,
  ],
  templateUrl: './zx-member-role-editor.component.html',
  styleUrl: './zx-member-role-editor.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [DropdownPopoverAnimation],
})
export class ZxMemberRoleEditorComponent implements OnChanges {
  @Input() members: MemberRoleItem[] = [];
  @Input() availableRoles: string[] = [];
  /** translate key prefix for role labels: `${roleLabelKey}.${role}` */
  @Input() roleLabelKey = '';
  @Input() showDates = true;
  @Input() memberTypes = 'author,authorAlias';
  @Input() addPlaceholder = '';
  @Input() roleAddPlaceholder = '';
  @Output() readonly fieldsChange = new EventEmitter<MemberFields>();
  @Output() readonly removeMember = new EventEmitter<number>();

  editable: EditableMember[] = [];

  readonly rolePositions: ConnectedPosition[] = [
    {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 4},
    {originX: 'start', originY: 'top', overlayX: 'start', overlayY: 'bottom', offsetY: -4},
  ];

  newAuthor: EntityRef | null = null;

  constructor(
    private readonly cdr: ChangeDetectorRef,
    private readonly translate: TranslateService,
  ) {}

  /** Selected roles as chips (id = role key, title = its translated label). */
  roleChips(roles: Set<string>): ChipItem[] {
    return [...roles].map(role => ({
      id: role,
      title: this.translate.instant(`${this.roleLabelKey}.${role}`),
    }));
  }

  onRoleChipRemoved(roles: Set<string>, chip: ChipItem): void {
    this.removeRole(roles, String(chip.id));
  }

  ngOnChanges(): void {
    this.editable = this.members.map(member => ({
      id: member.id,
      title: member.title,
      persisted: true,
      startDate: member.startDate,
      endDate: member.endDate,
      roles: new Set(member.roles),
      roleQuery: '',
      roleFocused: false,
      roleActiveIndex: 0,
    }));
    this.emit();
  }

  /** Roles still available for a member, filtered by its role search query. */
  availableFor(roles: Set<string>, query: string): string[] {
    const normalized = query.trim().toLowerCase();
    return this.availableRoles.filter(
      role => !roles.has(role) && role.toLowerCase().includes(normalized),
    );
  }

  addRole(roles: Set<string>, role: string): void {
    roles.add(role);
    this.emit();
  }

  onNewAuthorSelected(author: EntityRef | null): void {
    this.newAuthor = author;
    if (!author) {
      return;
    }

    if (!this.editable.some(member => member.id === author.id)) {
      this.editable = [
        ...this.editable,
        {
          id: author.id,
          title: author.title,
          persisted: false,
          startDate: '',
          endDate: '',
          roles: new Set<string>(),
          roleQuery: '',
          roleFocused: false,
          roleActiveIndex: 0,
        },
      ];
      this.emit();
    }

    setTimeout(() => {
      this.newAuthor = null;
      this.cdr.markForCheck();
    });
  }

  /** Reopen the options and reset the highlight whenever the query changes. */
  onRoleQueryChange(target: EditableMember, value: string): void {
    target.roleQuery = value;
    target.roleFocused = true;
    target.roleActiveIndex = 0;
  }

  /** Keyboard navigation for the role options overlay (↑/↓/Enter/Esc). */
  onRoleKeydown(target: EditableMember, event: KeyboardEvent): void {
    const options = this.availableFor(target.roles, target.roleQuery);
    const current = target.roleActiveIndex;

    const action = listKeyboardNav(event.key, current, options.length);
    let index = current;
    switch (action.kind) {
      case 'move':
        event.preventDefault();
        index = action.index;
        break;
      case 'select': {
        // Prevent the surrounding form from submitting on selection.
        event.preventDefault();
        const role = options[action.index];
        if (role) {
          this.addRole(target.roles, role);
        }
        index = 0;
        break;
      }
      case 'close':
        target.roleFocused = false;
        return;
      default:
        return;
    }

    target.roleActiveIndex = index;
  }

  removeRole(roles: Set<string>, role: string): void {
    roles.delete(role);
    this.emit();
  }

  onRoleBlur(target: EditableMember): void {
    // Delay so a click on an option is registered before the list collapses.
    setTimeout(() => {
      target.roleFocused = false;
      target.roleQuery = '';
      this.cdr.markForCheck();
    }, 150);
  }

  onRemove(member: EditableMember): void {
    this.editable = this.editable.filter(item => item !== member);
    if (member.persisted) {
      this.removeMember.emit(member.id);
    }
    this.emit();
  }

  onChanged(): void {
    this.emit();
  }

  private emit(): void {
    const role: Record<string, string[]> = {};
    const start: Record<string, string> = {};
    const end: Record<string, string> = {};

    for (const member of this.editable) {
      role[member.id] = member.roles.size > 0 ? [...member.roles] : ['unknown'];
      start[member.id] = member.startDate;
      end[member.id] = member.endDate;
    }

    this.fieldsChange.emit({
      addAuthorRole: role,
      addAuthorStartDate: start,
      addAuthorEndDate: end,
    });
  }
}
