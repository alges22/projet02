import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InscriptionAbsenceComponent } from './inscription-absence.component';

describe('InscriptionAbsenceComponent', () => {
  let component: InscriptionAbsenceComponent;
  let fixture: ComponentFixture<InscriptionAbsenceComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ InscriptionAbsenceComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InscriptionAbsenceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
