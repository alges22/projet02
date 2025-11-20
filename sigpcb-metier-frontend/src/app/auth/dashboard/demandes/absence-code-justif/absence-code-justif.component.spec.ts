import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AbsenceCodeJustifComponent } from './absence-code-justif.component';

describe('AbsenceCodeJustifComponent', () => {
  let component: AbsenceCodeJustifComponent;
  let fixture: ComponentFixture<AbsenceCodeJustifComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AbsenceCodeJustifComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AbsenceCodeJustifComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
