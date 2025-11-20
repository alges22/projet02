import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandeExaminateurComponent } from './demande-examinateur.component';

describe('DemandeExaminateurComponent', () => {
  let component: DemandeExaminateurComponent;
  let fixture: ComponentFixture<DemandeExaminateurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DemandeExaminateurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandeExaminateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
