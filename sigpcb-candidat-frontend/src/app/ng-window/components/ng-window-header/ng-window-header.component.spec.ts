import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NgWindowHeaderComponent } from './ng-window-header.component';

describe('NgWindowHeaderComponent', () => {
  let component: NgWindowHeaderComponent;
  let fixture: ComponentFixture<NgWindowHeaderComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ NgWindowHeaderComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NgWindowHeaderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
