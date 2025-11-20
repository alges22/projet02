import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BlueAsideComponent } from './blue-aside.component';

describe('BlueAsideComponent', () => {
  let component: BlueAsideComponent;
  let fixture: ComponentFixture<BlueAsideComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BlueAsideComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BlueAsideComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
