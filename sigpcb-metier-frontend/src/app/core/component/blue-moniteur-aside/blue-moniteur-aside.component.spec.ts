import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BlueMoniteurAsideComponent } from './blue-moniteur-aside.component';

describe('BlueMoniteurAsideComponent', () => {
  let component: BlueMoniteurAsideComponent;
  let fixture: ComponentFixture<BlueMoniteurAsideComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BlueMoniteurAsideComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BlueMoniteurAsideComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
