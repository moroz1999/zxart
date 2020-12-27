import {StructureElement} from './structure-element';

export class ZxProd implements StructureElement {
  id: number;

  constructor(id: number) {
    this.id = id;
  }
}
