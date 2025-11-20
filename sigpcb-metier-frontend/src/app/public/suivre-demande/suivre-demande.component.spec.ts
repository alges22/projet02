import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuivreDemandeComponent } from './suivre-demande.component';

describe('SuivreDemandeComponent', () => {
  let component: SuivreDemandeComponent;
  let fixture: ComponentFixture<SuivreDemandeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SuivreDemandeComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SuivreDemandeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
