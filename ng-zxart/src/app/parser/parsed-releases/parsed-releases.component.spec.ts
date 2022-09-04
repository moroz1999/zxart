import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ParsedReleasesComponent } from './parsed-releases.component';

describe('ParsedReleasesComponent', () => {
  let component: ParsedReleasesComponent;
  let fixture: ComponentFixture<ParsedReleasesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ParsedReleasesComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ParsedReleasesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
