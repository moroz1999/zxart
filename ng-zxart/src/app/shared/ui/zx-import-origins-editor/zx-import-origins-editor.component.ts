import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxCloseButtonComponent} from '../zx-close-button/zx-close-button.component';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxSelectComponent, ZxSelectOption} from '../zx-select/zx-select.component';
import {ImportOriginFields, ImportOriginItem} from './zx-import-origins-editor.models';

/**
 * Editor of the ids an entity carries on external portals — the pairs the
 * outgoing links on its page are built from. Every entity that was imported
 * from another portal edits them here: one row per id, with the portal picked
 * from the options the backend offers and the id typed beside it. The same
 * portal may appear in several rows, because an entity can hold more than one
 * id there.
 *
 * Emits the whole list on every change, indexed by row, so the form submits
 * exactly what is on screen and the backend drops what it no longer carries.
 */
@Component({
  selector: 'zx-import-origins-editor',
  standalone: true,
  imports: [
    FormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxCloseButtonComponent,
    ZxInputComponent,
    ZxSelectComponent,
  ],
  templateUrl: './zx-import-origins-editor.component.html',
  styleUrl: './zx-import-origins-editor.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxImportOriginsEditorComponent implements OnChanges {
  @Input() origins: ImportOriginItem[] = [];
  @Input() options: ZxSelectOption[] = [];
  @Output() readonly fieldsChange = new EventEmitter<ImportOriginFields>();

  rows: ImportOriginItem[] = [];

  ngOnChanges(): void {
    this.rows = this.origins.map(origin => ({...origin}));
    this.emit();
  }

  onAdd(): void {
    this.rows = [...this.rows, {origin: this.options[0]?.value ?? '', importId: ''}];
    this.emit();
  }

  onRemove(row: ImportOriginItem): void {
    this.rows = this.rows.filter(item => item !== row);
    this.emit();
  }

  onChanged(): void {
    this.emit();
  }

  private emit(): void {
    const fields: ImportOriginFields = {};
    this.rows.forEach((row, index) => {
      fields[index] = {origin: row.origin, importId: row.importId};
    });
    this.fieldsChange.emit(fields);
  }
}
