import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InscriptionExamenComponent } from './inscription-examen.component';

describe('InscriptionExamenComponent', () => {
  let component: InscriptionExamenComponent;
  let fixture: ComponentFixture<InscriptionExamenComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ InscriptionExamenComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InscriptionExamenComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
