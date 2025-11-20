import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BlueAsideRightComponent } from './blue-aside-right.component';

describe('BlueAsideRightComponent', () => {
  let component: BlueAsideRightComponent;
  let fixture: ComponentFixture<BlueAsideRightComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BlueAsideRightComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BlueAsideRightComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
