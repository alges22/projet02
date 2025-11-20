import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PermisInterFicheComponent } from './permis-inter-fiche.component';

describe('PermisInterFicheComponent', () => {
  let component: PermisInterFicheComponent;
  let fixture: ComponentFixture<PermisInterFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PermisInterFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PermisInterFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
