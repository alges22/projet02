import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DossierInitComponent } from './dossier-init.component';

describe('DossierInitComponent', () => {
  let component: DossierInitComponent;
  let fixture: ComponentFixture<DossierInitComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DossierInitComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DossierInitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
