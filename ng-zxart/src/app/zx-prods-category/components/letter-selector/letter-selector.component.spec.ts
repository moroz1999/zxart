import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LetterSelectorComponent } from './letter-selector.component';

describe('LetterSelectorComponent', () => {
  let component: LetterSelectorComponent;
  let fixture: ComponentFixture<LetterSelectorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LetterSelectorComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(LetterSelectorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
