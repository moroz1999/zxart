import {Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA} from '@angular/material/dialog';
import {SelectorDto} from '../../../models/selector-dto';

@Component({
  selector: 'app-dialog-selector-dialog',
  templateUrl: './dialog-selector-dialog.component.html',
  styleUrls: ['./dialog-selector-dialog.component.scss'],
})
export class DialogSelectorDialogComponent implements OnInit {
  selectedValues: { [key: string]: boolean; } = {};

  constructor(
    @Inject(MAT_DIALOG_DATA) public selectorData: SelectorDto,
  ) {
    for (const group of selectorData) {
      for (const value of group.values) {
        if (value.selected) {
          this.selectedValues[value.value] = true;
        }
      }
    }
  }

  ngOnInit(): void {
  }

}
