import {Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA} from '@angular/material/dialog';
import {SelectorDto} from '../../../models/selector-dto';

@Component({
  selector: 'app-year-selector-dialog',
  templateUrl: './year-selector-dialog.component.html',
  styleUrls: ['./year-selector-dialog.component.scss'],
})
export class YearSelectorDialogComponent implements OnInit {
  selectedYears: { [key: string]: boolean; } = {};

  constructor(
    @Inject(MAT_DIALOG_DATA) public yearsSelector: SelectorDto,
  ) {
    for (const year of yearsSelector) {
      if (year.selected) {
        this.selectedYears[year.value] = true;
      }
    }
  }

  ngOnInit(): void {
  }

}
