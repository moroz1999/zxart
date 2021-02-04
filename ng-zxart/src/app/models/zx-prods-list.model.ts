import {StructureElement} from './structure-element';

export class ZxProdsList implements StructureElement {
  constructor(
    public id: number,
    public title: string
  ) {
  }
}
