import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {SelectorDto, SelectorValue} from '../../models/selector-dto';

@Component({
  selector: 'app-sorting-selector',
  templateUrl: './sorting-selector.component.html',
  styleUrls: ['./sorting-selector.component.scss'],
})
export class SortingSelectorComponent implements OnInit {
  @Input() sortingSelector!: SelectorDto;
  @Output() sortingSelected = new EventEmitter<string>();
  sorting: string = '';

  constructor() {
  }

  ngOnInit(): void {
    for (const group of this.sortingSelector) {
      for (const sorting of group.values) {
        if (sorting.selected) {
          this.sorting = sorting.value;
        }
      }
    }
  }

  dataChanged(value: string) {
    this.sortingSelected.emit(value);
  }
}
