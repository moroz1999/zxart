import {ComponentFixture, TestBed} from '@angular/core/testing';

import {ZxProdBlockComponent} from './zx-prod-block.component';

describe('ZxProdComponent', () => {
    let component: ZxProdBlockComponent;
    let fixture: ComponentFixture<ZxProdBlockComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            declarations: [ZxProdBlockComponent],
        })
            .compileComponents();
    });

    beforeEach(() => {
        fixture = TestBed.createComponent(ZxProdBlockComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
