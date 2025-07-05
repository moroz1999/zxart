import {ComponentFixture, TestBed} from '@angular/core/testing';

import {ZxProdsListComponent} from './zx-prods-list.component';

describe('ZxProdsListComponent', () => {
    let component: ZxProdsListComponent;
    let fixture: ComponentFixture<ZxProdsListComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            declarations: [ZxProdsListComponent],
        })
            .compileComponents();
    });

    beforeEach(() => {
        fixture = TestBed.createComponent(ZxProdsListComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
