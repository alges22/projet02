import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandeEchangePermisComponent } from './demande-echange-permis.component';

describe('DemandeEchangePermisComponent', () => {
  let component: DemandeEchangePermisComponent;
  let fixture: ComponentFixture<DemandeEchangePermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DemandeEchangePermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandeEchangePermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
