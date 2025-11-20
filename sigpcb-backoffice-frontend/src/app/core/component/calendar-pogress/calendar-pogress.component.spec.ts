import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CalendarPogressComponent } from './calendar-pogress.component';

describe('CalendarPogressComponent', () => {
  let component: CalendarPogressComponent;
  let fixture: ComponentFixture<CalendarPogressComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CalendarPogressComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CalendarPogressComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
