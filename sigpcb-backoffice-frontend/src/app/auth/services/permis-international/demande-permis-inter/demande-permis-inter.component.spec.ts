import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandePermisInterComponent } from './demande-permis-inter.component';

describe('DemandePermisInterComponent', () => {
  let component: DemandePermisInterComponent;
  let fixture: ComponentFixture<DemandePermisInterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DemandePermisInterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandePermisInterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
