import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FilterSystemComponent } from './filter-system.component';

describe('FilterSystemComponent', () => {
  let component: FilterSystemComponent;
  let fixture: ComponentFixture<FilterSystemComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ FilterSystemComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(FilterSystemComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
