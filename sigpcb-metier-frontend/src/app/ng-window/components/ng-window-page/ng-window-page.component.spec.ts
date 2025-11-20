import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NgWindowPageComponent } from './ng-window-page.component';

describe('NgWindowPageComponent', () => {
  let component: NgWindowPageComponent;
  let fixture: ComponentFixture<NgWindowPageComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ NgWindowPageComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NgWindowPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
