import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DossierRejectedComponent } from './dossier-rejected.component';

describe('DossierRejectedComponent', () => {
  let component: DossierRejectedComponent;
  let fixture: ComponentFixture<DossierRejectedComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DossierRejectedComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DossierRejectedComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
