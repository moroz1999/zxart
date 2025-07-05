import {StructureElement} from './structure-element';
import {TagDto} from './tag-dto';

export class Tag extends StructureElement {
    title = '';
    value = '';
    synonym = '';
    description = '';

    constructor(data: TagDto) {
        super(data);
        this.title = data.title;
        this.value = data.value;
        this.synonym = data.synonym;
        this.description = data.description;
    }
}
