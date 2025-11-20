import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ParamBaseComponent } from './param-base.component';

describe('ParamBaseComponent', () => {
  let component: ParamBaseComponent;
  let fixture: ComponentFixture<ParamBaseComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ParamBaseComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ParamBaseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
