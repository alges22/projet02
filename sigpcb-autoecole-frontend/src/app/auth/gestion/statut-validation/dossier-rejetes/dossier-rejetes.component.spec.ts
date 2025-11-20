import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DossierRejetesComponent } from './dossier-rejetes.component';

describe('DossierRejetesComponent', () => {
  let component: DossierRejetesComponent;
  let fixture: ComponentFixture<DossierRejetesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DossierRejetesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DossierRejetesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
