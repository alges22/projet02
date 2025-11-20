import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BlueAsideLeftComponent } from './blue-aside-left.component';

describe('BlueAsideLeftComponent', () => {
  let component: BlueAsideLeftComponent;
  let fixture: ComponentFixture<BlueAsideLeftComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BlueAsideLeftComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BlueAsideLeftComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
