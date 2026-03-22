import {
  ChangeDetectionStrategy,
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges
} from '@angular/core';
import {NgForOf} from '@angular/common';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {CategoriesSelectorDto, CategorySelectorDto} from '../../models/categories-selector-dto';
import {CategoriesTreeNodeComponent} from './categories-tree-node.component';
import {environment} from '../../../../../environments/environment';

@Component({
    selector: 'zx-categories-tree-selector',
    templateUrl: './categories-tree-selector.component.html',
    styleUrls: ['./categories-tree-selector.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [NgForOf, CategoriesTreeNodeComponent],
})
export class CategoriesTreeSelectorComponent implements OnInit, OnChanges {
    @Input() selectorData!: CategoriesSelectorDto;
    @Output() categoryChanged = new EventEmitter<number>();

    expandedIds = new Set<number>();

    constructor(private iconReg: SvgIconRegistryService) {}

    ngOnInit(): void {
        this.iconReg.loadSvg(`${environment.svgUrl}expand-more.svg`, 'expand-more')?.subscribe();
    }

    ngOnChanges(changes: SimpleChanges): void {
        if (changes['selectorData']) {
            const ids = new Set<number>();
            const collect = (node: CategorySelectorDto) => {
                if (node.selected) {
                    ids.add(node.id);
                }
                node.children?.forEach(collect);
            };
            this.selectorData?.forEach(collect);
            this.expandedIds = ids;
        }
    }

    nodeClicked(id: number): void {
        this.categoryChanged.emit(id);
    }

    toggleExpanded(id: number): void {
        const next = new Set(this.expandedIds);
        if (next.has(id)) {
            next.delete(id);
        } else {
            next.add(id);
        }
        this.expandedIds = next;
    }
}
