import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandeProrogationComponent } from './demande-prorogation.component';

describe('DemandeProrogationComponent', () => {
  let component: DemandeProrogationComponent;
  let fixture: ComponentFixture<DemandeProrogationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DemandeProrogationComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandeProrogationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
