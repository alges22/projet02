import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NgWindowComponent } from './ng-window.component';

describe('NgWindowComponent', () => {
  let component: NgWindowComponent;
  let fixture: ComponentFixture<NgWindowComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ NgWindowComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NgWindowComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
