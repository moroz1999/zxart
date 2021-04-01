import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {SelectorValue} from '../../models/selector-dto';

@Component({
  selector: 'app-sorting-selector',
  templateUrl: './sorting-selector.component.html',
  styleUrls: ['./sorting-selector.component.scss'],
})
export class SortingSelectorComponent implements OnInit {
  @Input() sortingSelector!: Array<SelectorValue>;
  @Output() sortingSelected = new EventEmitter<string>();
  sorting: string = '';

  constructor() {
  }

  ngOnInit(): void {
    for (const sorting of this.sortingSelector) {
      if (sorting.selected) {
        this.sorting = sorting.value;
      }
    }
  }

  dataChanged(value: string) {
    this.sortingSelected.emit(value);
  }
}
