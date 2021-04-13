import {Component, Input, OnInit, Output, EventEmitter, OnChanges} from '@angular/core';
import {MatDialog} from '@angular/material/dialog';
import {DialogSelectorDialogComponent} from './dialog-selector-dialog/dialog-selector-dialog.component';
import {SelectorDto} from '../../models/selector-dto';

@Component({
  selector: 'app-dialog-selector',
  templateUrl: './dialog-selector.component.html',
  styleUrls: ['./dialog-selector.component.scss'],
})
export class DialogSelectorComponent implements OnInit, OnChanges {
  @Input() selectorData!: SelectorDto;
  @Input() selectedValuesLabel!: string;
  @Input() selectValuesLabel!: string;
  selectedValues: Array<string> = [];
  @Output() newValues = new EventEmitter<Array<string>>();
  public value = 0;

  constructor(
    public dialog: MatDialog,
  ) {
  }

  ngOnInit(): void {

  }

  ngOnChanges() {
    this.selectedValues = [];
    if (this.selectorData) {
      for (const group of this.selectorData) {
        for (const value of group.values) {
          if (value.selected) {
            this.selectedValues.push(value.title);
          }
        }
      }
    }
  }

  clickHandler() {
    let dialogRef = this.dialog.open(DialogSelectorDialogComponent, {
      width: '30rem',
      data: this.selectorData,
    });
    dialogRef.afterClosed().subscribe((result: { [key: string]: boolean; }) => {
      let values = [] as Array<string>;
      if (result) {
        for (let value in result) {
          if (result.hasOwnProperty(value)) {
            if (result[value]) {
              values.push(value);
            }
          }
        }
      }
      this.newValues.emit(values);
    });
  }

  getSelectedValuesString() {
    return this.selectedValues.join(', ');
  }
}
