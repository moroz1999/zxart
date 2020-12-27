import {StructureElement} from './structure-element';

export class ZxProdsList implements StructureElement {
  id: number;
  title: string;

  constructor(
    id: number,
    title: string
  ) {
    this.id = id;
    this.title = title;
  }
}
