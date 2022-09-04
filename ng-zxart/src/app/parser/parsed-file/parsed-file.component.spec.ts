import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ParsedFileComponent } from './parsed-file.component';

describe('ParsedFileComponent', () => {
  let component: ParsedFileComponent;
  let fixture: ComponentFixture<ParsedFileComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ParsedFileComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ParsedFileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
