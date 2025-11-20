import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GestDashboardComponent } from './gest-dashboard.component';

describe('GestDashboardComponent', () => {
  let component: GestDashboardComponent;
  let fixture: ComponentFixture<GestDashboardComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GestDashboardComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GestDashboardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
