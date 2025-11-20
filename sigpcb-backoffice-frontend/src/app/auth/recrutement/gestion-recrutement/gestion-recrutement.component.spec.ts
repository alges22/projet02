import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GestionRecrutementComponent } from './gestion-recrutement.component';

describe('GestionRecrutementComponent', () => {
  let component: GestionRecrutementComponent;
  let fixture: ComponentFixture<GestionRecrutementComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GestionRecrutementComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GestionRecrutementComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
