import {ComponentFixture, TestBed} from '@angular/core/testing';

import {ParsedReleaseComponent} from './parsed-release.component';

describe('ParsedReleaseComponent', () => {
    let component: ParsedReleaseComponent;
    let fixture: ComponentFixture<ParsedReleaseComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            declarations: [ParsedReleaseComponent],
        })
            .compileComponents();
    });

    beforeEach(() => {
        fixture = TestBed.createComponent(ParsedReleaseComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
