import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AutoEcoleFicheComponent } from './auto-ecole-fiche.component';

describe('AutoEcoleFicheComponent', () => {
  let component: AutoEcoleFicheComponent;
  let fixture: ComponentFixture<AutoEcoleFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AutoEcoleFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AutoEcoleFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
