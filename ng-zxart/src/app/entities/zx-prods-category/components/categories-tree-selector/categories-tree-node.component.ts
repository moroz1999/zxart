import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {NgForOf, NgIf} from '@angular/common';
import {SvgIconComponent} from 'angular-svg-icon';
import {CategorySelectorDto} from '../../models/categories-selector-dto';

@Component({
    selector: 'zx-categories-tree-node',
    templateUrl: './categories-tree-node.component.html',
    styleUrls: ['./categories-tree-node.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [NgForOf, NgIf, SvgIconComponent],
})
export class CategoriesTreeNodeComponent {
    @Input() node!: CategorySelectorDto;
    @Input() expandedIds!: Set<number>;
    @Output() nodeClicked = new EventEmitter<number>();
    @Output() toggleExpanded = new EventEmitter<number>();

    get hasChildren(): boolean {
        return !!this.node.children?.length;
    }

    get isExpanded(): boolean {
        return this.expandedIds.has(this.node.id);
    }

    onNodeClick(event: Event): void {
        event.preventDefault();
        this.nodeClicked.emit(this.node.id);
    }

    onToggle(): void {
        this.toggleExpanded.emit(this.node.id);
    }
}
