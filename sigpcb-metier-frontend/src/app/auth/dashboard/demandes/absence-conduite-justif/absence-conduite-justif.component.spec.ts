import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AbsenceConduiteJustifComponent } from './absence-conduite-justif.component';

describe('AbsenceConduiteJustifComponent', () => {
  let component: AbsenceConduiteJustifComponent;
  let fixture: ComponentFixture<AbsenceConduiteJustifComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AbsenceConduiteJustifComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AbsenceConduiteJustifComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
