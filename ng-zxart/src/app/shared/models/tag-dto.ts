import {StructureElementDto} from './structure-element-dto';

export interface TagDto extends StructureElementDto {
    readonly title: string;
    readonly value: string;
    readonly synonym: string;
    readonly description: string;
}
