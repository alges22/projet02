import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NgWindowBtnComponent } from './ng-window-btn.component';

describe('NgWindowBtnComponent', () => {
  let component: NgWindowBtnComponent;
  let fixture: ComponentFixture<NgWindowBtnComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ NgWindowBtnComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NgWindowBtnComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
