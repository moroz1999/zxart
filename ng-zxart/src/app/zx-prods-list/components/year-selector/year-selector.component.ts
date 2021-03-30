import {Component, Input, OnInit, Output, EventEmitter, OnChanges, SimpleChanges} from '@angular/core';
import {MatDialog} from '@angular/material/dialog';
import {YearSelectorDialogComponent} from './year-selector-dialog/year-selector-dialog.component';
import {SelectorDto, SelectorValue} from '../../models/selector-dto';

@Component({
  selector: 'app-year-selector',
  templateUrl: './year-selector.component.html',
  styleUrls: ['./year-selector.component.scss'],
})
export class YearSelectorComponent implements OnInit, OnChanges {
  @Input() yearsSelector!: SelectorDto;
  selectedYears: Array<string> = [];
  @Output() newValues = new EventEmitter<Array<number>>();
  public value = 0;

  constructor(
    public dialog: MatDialog,
  ) {
  }

  ngOnInit(): void {

  }

  ngOnChanges(changes: SimpleChanges) {
    this.selectedYears = [];
    for (const year of this.yearsSelector) {
      if (year.selected) {
        this.selectedYears.push(year.title);
      }
    }
  }

  clickHandler() {
    let dialogRef = this.dialog.open(YearSelectorDialogComponent, {
      width: '30rem',
      data: this.yearsSelector,
    });
    dialogRef.afterClosed().subscribe((result: { [key: number]: boolean; }) => {
      let years = [] as Array<number>;
      if (result) {
        for (let year in result) {
          if (result.hasOwnProperty(year)) {
            if (result[year]) {
              years.push(+year);
            }
          }
        }
      }
      this.newValues.emit(years);
    });
  }

  getSelectedYearsString() {
    return this.selectedYears.join(', ');
  }
}
