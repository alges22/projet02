import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RecrutementExaminateurFicheComponent } from './recrutement-examinateur-fiche.component';

describe('RecrutementExaminateurFicheComponent', () => {
  let component: RecrutementExaminateurFicheComponent;
  let fixture: ComponentFixture<RecrutementExaminateurFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RecrutementExaminateurFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RecrutementExaminateurFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
