import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ProrogationFicheComponent } from './prorogation-fiche.component';

describe('ProrogationFicheComponent', () => {
  let component: ProrogationFicheComponent;
  let fixture: ComponentFixture<ProrogationFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ProrogationFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ProrogationFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
