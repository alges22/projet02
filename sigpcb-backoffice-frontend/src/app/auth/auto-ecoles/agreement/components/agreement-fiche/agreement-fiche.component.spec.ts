import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AgreementFicheComponent } from './agreement-fiche.component';

describe('AgreementFicheComponent', () => {
  let component: AgreementFicheComponent;
  let fixture: ComponentFixture<AgreementFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AgreementFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AgreementFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
