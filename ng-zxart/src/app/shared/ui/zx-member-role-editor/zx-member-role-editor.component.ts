import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {EntityRef} from '../../models/entity-ref';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxEntityAutocompleteComponent} from '../zx-entity-autocomplete/zx-entity-autocomplete.component';
import {MemberFields, MemberRoleItem} from './zx-member-role-editor.models';

interface EditableMember {
  id: number;
  title: string;
  startDate: string;
  endDate: string;
  roles: Set<string>;
}

/**
 * Member / role manager for an entity's authorship (e.g. a group's members).
 * Lists current members with editable roles and active period, and an add-member
 * row (author picker + roles/dates). Emits the legacy `addAuthor*` field values
 * for the form save. Member removal is emitted so the host can run the
 * `deleteAuthor` action live (no page reload).
 */
@Component({
  selector: 'zx-member-role-editor',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxInputComponent,
    ZxEntityAutocompleteComponent,
  ],
  templateUrl: './zx-member-role-editor.component.html',
  styleUrl: './zx-member-role-editor.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxMemberRoleEditorComponent implements OnChanges {
  @Input() members: MemberRoleItem[] = [];
  @Input() availableRoles: string[] = [];
  /** translate key prefix for role labels: `${roleLabelKey}.${role}` */
  @Input() roleLabelKey = '';
  @Input() showDates = true;
  @Input() memberTypes = 'author,authorAlias';
  @Output() readonly fieldsChange = new EventEmitter<MemberFields>();
  @Output() readonly removeMember = new EventEmitter<number>();

  editable: EditableMember[] = [];

  newAuthor: EntityRef | null = null;
  newStartDate = '';
  newEndDate = '';
  newRoles = new Set<string>();

  ngOnChanges(): void {
    this.editable = this.members.map(member => ({
      id: member.id,
      title: member.title,
      startDate: member.startDate,
      endDate: member.endDate,
      roles: new Set(member.roles),
    }));
    this.emit();
  }

  hasRole(roles: Set<string>, role: string): boolean {
    return roles.has(role);
  }

  toggleRole(roles: Set<string>, role: string, checked: boolean): void {
    if (checked) {
      roles.add(role);
    } else {
      roles.delete(role);
    }
    this.emit();
  }

  onRemove(id: number): void {
    this.editable = this.editable.filter(member => member.id !== id);
    this.removeMember.emit(id);
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
      role[member.id] = [...member.roles];
      start[member.id] = member.startDate;
      end[member.id] = member.endDate;
    }

    if (this.newAuthor) {
      role['new'] = [...this.newRoles];
      start['new'] = this.newStartDate;
      end['new'] = this.newEndDate;
    }

    this.fieldsChange.emit({
      addAuthor: this.newAuthor ? String(this.newAuthor.id) : '',
      addAuthorRole: role,
      addAuthorStartDate: start,
      addAuthorEndDate: end,
    });
  }
}
