import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PaiementTitresDeviresComponent } from './paiement-titres-devires.component';

describe('PaiementTitresDeviresComponent', () => {
  let component: PaiementTitresDeviresComponent;
  let fixture: ComponentFixture<PaiementTitresDeviresComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PaiementTitresDeviresComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PaiementTitresDeviresComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
