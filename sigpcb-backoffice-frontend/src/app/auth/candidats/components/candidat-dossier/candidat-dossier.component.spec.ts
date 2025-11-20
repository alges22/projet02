import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CandidatDossierComponent } from './candidat-dossier.component';

describe('CandidatDossierComponent', () => {
  let component: CandidatDossierComponent;
  let fixture: ComponentFixture<CandidatDossierComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CandidatDossierComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CandidatDossierComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
