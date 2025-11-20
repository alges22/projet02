import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LicenceHistoriqComponent } from './licence-historiq.component';

describe('LicenceHistoriqComponent', () => {
  let component: LicenceHistoriqComponent;
  let fixture: ComponentFixture<LicenceHistoriqComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LicenceHistoriqComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LicenceHistoriqComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
