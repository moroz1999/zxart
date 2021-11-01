import {Directive, EventEmitter, Input, Output} from '@angular/core';
import {ZxProd} from '../models/zx-prod';

@Directive()
export abstract class ZxProdComponent {
  @Input() model!: ZxProd;
  @Output() categoryChanged = new EventEmitter<number>();
  @Output() yearChanged = new EventEmitter<Array<string>>();
  @Output() hardwareChanged = new EventEmitter<Array<string>>();
  @Output() languageChanged = new EventEmitter<Array<string>>();

  categoryClicked(event: Event, categoryId: number): void {
    event.preventDefault();
    this.categoryChanged.emit(categoryId);
  }

  hardwareClicked(event: Event, hardware: string): void {
    event.preventDefault();
    this.hardwareChanged.emit([hardware]);
  }

  yearClicked(event: Event, year: string): void {
    event.preventDefault();
    this.yearChanged.emit([year]);
  }

  languageClicked(event: Event, language: string): void {
    event.preventDefault();
    this.languageChanged.emit([language]);
  }

  vote(rating: number) {

  }
}
