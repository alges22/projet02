import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CandidatDossierSessionComponent } from './candidat-dossier-session.component';

describe('CandidatDossierSessionComponent', () => {
  let component: CandidatDossierSessionComponent;
  let fixture: ComponentFixture<CandidatDossierSessionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CandidatDossierSessionComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CandidatDossierSessionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
