import {Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA} from '@angular/material/dialog';
import {YearSelectorDto} from '../../../models/year-selector-dto';


@Component({
  selector: 'app-year-selector-dialog',
  templateUrl: './year-selector-dialog.component.html',
  styleUrls: ['./year-selector-dialog.component.scss'],
})
export class YearSelectorDialogComponent implements OnInit {
  selectedYears: { [key: number]: boolean; } = {};

  constructor(
    @Inject(MAT_DIALOG_DATA) public yearsSelector: YearSelectorDto,
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
