import {Component, Input, OnInit, Output, EventEmitter} from '@angular/core';
import {MatDialog} from '@angular/material/dialog';
import {YearSelectorDialogComponent} from './year-selector-dialog/year-selector-dialog.component';

@Component({
  selector: 'app-year-selector',
  templateUrl: './year-selector.component.html',
  styleUrls: ['./year-selector.component.scss'],
})
export class YearSelectorComponent implements OnInit {
  @Input() years!: Array<number>;
  @Output() selectedYears = new EventEmitter<Array<number>>();
  public value = 0;

  constructor(
    public dialog: MatDialog,
  ) {
  }

  ngOnInit(): void {
  }

  clickHandler() {
    let dialogRef = this.dialog.open(YearSelectorDialogComponent, {
      width: '30rem',
      data: this.years,
    });
    dialogRef.afterClosed().subscribe(result => {
      let years = [] as Array<number>;
      if (result) {
        years = Object.keys(result).map(year => parseInt(year, 10));
      }
      this.selectedYears.emit(years);
    });
  }
}
