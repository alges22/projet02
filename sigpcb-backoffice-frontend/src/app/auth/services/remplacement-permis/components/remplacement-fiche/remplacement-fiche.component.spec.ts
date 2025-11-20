import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RemplacementFicheComponent } from './remplacement-fiche.component';

describe('RemplacementFicheComponent', () => {
  let component: RemplacementFicheComponent;
  let fixture: ComponentFixture<RemplacementFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RemplacementFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RemplacementFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
