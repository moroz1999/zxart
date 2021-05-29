import {Component, EventEmitter, Input, OnChanges, OnInit, Output} from '@angular/core';
import {SelectorDto} from '../../models/selector-dto';

@Component({
  selector: 'app-letter-selector',
  templateUrl: './letter-selector.component.html',
  styleUrls: ['./letter-selector.component.scss'],
})
export class LetterSelectorComponent implements OnChanges {
  @Input() lettersSelector!: SelectorDto;
  @Output() letterSelected = new EventEmitter<string>();
  selectedLetter = '';

  constructor() {
  }

  ngOnChanges(): void {
    this.selectedLetter = '';
    if (this.lettersSelector && this.lettersSelector[0]) {
      for (let letter of this.lettersSelector[0].values) {
        if (letter.selected) {
          this.selectedLetter = letter.value;
          break;
        }
      }
    }
  }

  selectLetter(letter: string) {
    this.letterSelected.emit(letter);
  }
}
