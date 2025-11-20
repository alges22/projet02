import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RecrutementMoniteurFicheComponent } from './recrutement-moniteur-fiche.component';

describe('RecrutementMoniteurFicheComponent', () => {
  let component: RecrutementMoniteurFicheComponent;
  let fixture: ComponentFixture<RecrutementMoniteurFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RecrutementMoniteurFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RecrutementMoniteurFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
