import {NestedTreeControl} from '@angular/cdk/tree';
import {Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges} from '@angular/core';
import {
    MatNestedTreeNode,
    MatTree,
    MatTreeNestedDataSource,
    MatTreeNode,
    MatTreeNodeDef,
    MatTreeNodeOutlet,
    MatTreeNodeToggle,
} from '@angular/material/tree';
import {CategoriesSelectorDto, CategorySelectorDto} from '../../models/categories-selector-dto';
import {MatIcon} from '@angular/material/icon';
import {NgClass} from '@angular/common';
import {MatIconButton} from '@angular/material/button';

@Component({
    selector: 'zx-categories-tree-selector',
    templateUrl: './categories-tree-selector.component.html',
    styleUrls: ['./categories-tree-selector.component.scss'],
    standalone: true,
    imports: [
        MatTree,
        MatTreeNode,
        MatNestedTreeNode,
        MatIcon,
        MatTreeNodeDef,
        MatTreeNodeOutlet,
        MatTreeNodeToggle,
        MatTreeNodeDef,
        NgClass,
        MatIconButton,
    ],
})
export class CategoriesTreeSelectorComponent implements OnInit, OnChanges {

    @Input() selectorData!: CategoriesSelectorDto;
    treeControl = new NestedTreeControl<CategorySelectorDto>(node => node.children);
    dataSource = new MatTreeNestedDataSource<CategorySelectorDto>();
    @Output() categoryChanged = new EventEmitter<number>();

    ngOnInit(): void {
    }

    ngOnChanges(changes: SimpleChanges) {
        if (changes.selectorData) {
            this.dataSource.data = this.selectorData;

            const expander = (node: CategorySelectorDto) => {
                node.selected ? this.treeControl.expand(node) : this.treeControl.collapse(node);
                if (node.children) {
                    for (const child of node.children) {
                        expander(child);
                    }
                }
            };
            for (const selector of this.selectorData) {
                expander(selector);
            }
        }
    }

    // isExpandable = (node: CategorySelectorDto) => node.selected;
    hasChild = (_: number, node: CategorySelectorDto) => !!node.children && node.children.length > 0;

    nodeClicked(event: Event, nodeId: number): void {
        event.preventDefault();
        this.categoryChanged.emit(nodeId);
    }
}
