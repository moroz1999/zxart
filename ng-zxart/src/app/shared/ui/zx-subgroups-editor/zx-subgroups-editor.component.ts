import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {EntityRef} from '../../models/entity-ref';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxEntityAutocompleteComponent} from '../zx-entity-autocomplete/zx-entity-autocomplete.component';

export interface SubgroupItem {
  id: number;
  title: string;
}

/**
 * Editor for a group's subgroups (`subGroupsSelector`). Lists current subgroups
 * with remove, plus a group picker to add new ones. Emits the full list of ids;
 * the form posts it so `persistSubGroupConnections` keeps exactly this set.
 */
@Component({
  selector: 'zx-subgroups-editor',
  standalone: true,
  imports: [CommonModule, FormsModule, ZxButtonComponent, ZxEntityAutocompleteComponent],
  templateUrl: './zx-subgroups-editor.component.html',
  styleUrl: './zx-subgroups-editor.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxSubgroupsEditorComponent implements OnChanges {
  @Input() subgroups: SubgroupItem[] = [];
  @Output() readonly idsChange = new EventEmitter<number[]>();

  items: SubgroupItem[] = [];
  newGroup: EntityRef | null = null;

  ngOnChanges(): void {
    this.items = [...this.subgroups];
    this.emit();
  }

  onSelected(group: EntityRef | null): void {
    if (group && !this.items.some(item => item.id === group.id)) {
      this.items = [...this.items, {id: group.id, title: group.title}];
      this.emit();
    }
    // clear the picker so the next subgroup can be added
    this.newGroup = null;
  }

  onRemove(id: number): void {
    this.items = this.items.filter(item => item.id !== id);
    this.emit();
  }

  private emit(): void {
    this.idsChange.emit(this.items.map(item => item.id));
  }
}
