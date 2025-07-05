import {StructureElementDto} from './structure-element-dto';

export abstract class StructureElement {
    public id: number;
    public url: string;

    protected constructor(data: StructureElementDto) {
        this.id = data.id;
        this.url = data.url;
    }
}
