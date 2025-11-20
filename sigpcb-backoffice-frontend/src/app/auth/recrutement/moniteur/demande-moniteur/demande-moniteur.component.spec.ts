import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandeMoniteurComponent } from './demande-moniteur.component';

describe('DemandeMoniteurComponent', () => {
  let component: DemandeMoniteurComponent;
  let fixture: ComponentFixture<DemandeMoniteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DemandeMoniteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandeMoniteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
