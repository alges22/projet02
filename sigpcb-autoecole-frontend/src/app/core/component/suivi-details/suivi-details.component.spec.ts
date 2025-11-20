import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuiviDetailsComponent } from './suivi-details.component';

describe('SuiviDetailsComponent', () => {
  let component: SuiviDetailsComponent;
  let fixture: ComponentFixture<SuiviDetailsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SuiviDetailsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SuiviDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
