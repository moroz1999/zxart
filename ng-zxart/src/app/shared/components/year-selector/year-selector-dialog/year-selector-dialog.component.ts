import {Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA} from '@angular/material/dialog';
import {KeyValue} from '@angular/common';


@Component({
  selector: 'app-year-selector-dialog',
  templateUrl: './year-selector-dialog.component.html',
  styleUrls: ['./year-selector-dialog.component.scss'],
})
export class YearSelectorDialogComponent implements OnInit {
  selectedYears: { [key: number]: number; } = {};

  constructor(
    @Inject(MAT_DIALOG_DATA) public years: Array<number>,
  ) {
  }

  ngOnInit(): void {
  }

}
