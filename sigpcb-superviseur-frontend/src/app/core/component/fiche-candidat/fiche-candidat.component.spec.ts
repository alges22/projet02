import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FicheCandidatComponent } from './fiche-candidat.component';

describe('FicheCandidatComponent', () => {
  let component: FicheCandidatComponent;
  let fixture: ComponentFixture<FicheCandidatComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ FicheCandidatComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(FicheCandidatComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
