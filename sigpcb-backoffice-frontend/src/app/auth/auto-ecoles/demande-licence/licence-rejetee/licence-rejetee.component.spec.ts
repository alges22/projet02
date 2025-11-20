import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LicenceRejeteeComponent } from './licence-rejetee.component';

describe('LicenceRejeteeComponent', () => {
  let component: LicenceRejeteeComponent;
  let fixture: ComponentFixture<LicenceRejeteeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LicenceRejeteeComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LicenceRejeteeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
