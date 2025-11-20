import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandeAgrementComponent } from './demande-agrement.component';

describe('DemandeAgrementComponent', () => {
  let component: DemandeAgrementComponent;
  let fixture: ComponentFixture<DemandeAgrementComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DemandeAgrementComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandeAgrementComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
