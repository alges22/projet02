import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandeAuthPermisComponent } from './demande-auth-permis.component';

describe('DemandeAuthPermisComponent', () => {
  let component: DemandeAuthPermisComponent;
  let fixture: ComponentFixture<DemandeAuthPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DemandeAuthPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandeAuthPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
