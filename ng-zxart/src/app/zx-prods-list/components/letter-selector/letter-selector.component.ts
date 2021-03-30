import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {SelectorValue} from '../../models/selector-dto';

@Component({
  selector: 'app-letter-selector',
  templateUrl: './letter-selector.component.html',
  styleUrls: ['./letter-selector.component.scss'],
})
export class LetterSelectorComponent implements OnInit {
  @Input() lettersSelector!: Array<SelectorValue>;
  @Output() letterSelected = new EventEmitter<string>();

  constructor() {
  }

  ngOnInit(): void {
  }

  selectLetter(letter: string) {
    this.letterSelected.emit(letter)
  }
}
