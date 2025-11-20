import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuivreMonDossierComponent } from './suivre-mon-dossier.component';

describe('SuivreMonDossierComponent', () => {
  let component: SuivreMonDossierComponent;
  let fixture: ComponentFixture<SuivreMonDossierComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SuivreMonDossierComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SuivreMonDossierComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
