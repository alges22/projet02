import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DossierValidesComponent } from './dossier-valides.component';

describe('DossierValidesComponent', () => {
  let component: DossierValidesComponent;
  let fixture: ComponentFixture<DossierValidesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DossierValidesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DossierValidesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
