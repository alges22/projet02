import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LicenceValideeComponent } from './licence-validee.component';

describe('LicenceValideeComponent', () => {
  let component: LicenceValideeComponent;
  let fixture: ComponentFixture<LicenceValideeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LicenceValideeComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LicenceValideeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
