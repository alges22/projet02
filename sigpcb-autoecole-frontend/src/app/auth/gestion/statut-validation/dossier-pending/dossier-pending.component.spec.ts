import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DossierPendingComponent } from './dossier-pending.component';

describe('DossierPendingComponent', () => {
  let component: DossierPendingComponent;
  let fixture: ComponentFixture<DossierPendingComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DossierPendingComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DossierPendingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
