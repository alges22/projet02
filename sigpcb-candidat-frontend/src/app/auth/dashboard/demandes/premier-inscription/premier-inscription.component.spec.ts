import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PremierInscriptionComponent } from './premier-inscription.component';

describe('PremierInscriptionComponent', () => {
  let component: PremierInscriptionComponent;
  let fixture: ComponentFixture<PremierInscriptionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PremierInscriptionComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PremierInscriptionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
