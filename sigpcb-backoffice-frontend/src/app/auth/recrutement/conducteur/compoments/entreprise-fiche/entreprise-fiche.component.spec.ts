import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntrepriseFicheComponent } from './entreprise-fiche.component';

describe('EntrepriseFicheComponent', () => {
  let component: EntrepriseFicheComponent;
  let fixture: ComponentFixture<EntrepriseFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntrepriseFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntrepriseFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
