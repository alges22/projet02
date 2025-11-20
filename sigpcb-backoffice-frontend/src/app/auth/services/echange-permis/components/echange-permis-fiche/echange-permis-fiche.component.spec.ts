import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EchangePermisFicheComponent } from './echange-permis-fiche.component';

describe('EchangePermisFicheComponent', () => {
  let component: EchangePermisFicheComponent;
  let fixture: ComponentFixture<EchangePermisFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EchangePermisFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EchangePermisFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
