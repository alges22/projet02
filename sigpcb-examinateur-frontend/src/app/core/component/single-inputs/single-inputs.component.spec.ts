import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SingleInputsComponent } from './single-inputs.component';

describe('SingleInputsComponent', () => {
  let component: SingleInputsComponent;
  let fixture: ComponentFixture<SingleInputsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SingleInputsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SingleInputsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
