import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LicenceFicheComponent } from './licence-fiche.component';

describe('LicenceFicheComponent', () => {
  let component: LicenceFicheComponent;
  let fixture: ComponentFixture<LicenceFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LicenceFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LicenceFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
