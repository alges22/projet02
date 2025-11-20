import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ParamBaseTopbarComponent } from './param-base-topbar.component';

describe('ParamBaseTopbarComponent', () => {
  let component: ParamBaseTopbarComponent;
  let fixture: ComponentFixture<ParamBaseTopbarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ParamBaseTopbarComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ParamBaseTopbarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
