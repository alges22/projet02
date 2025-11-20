import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LaisserPasserComponent } from './laisser-passer.component';

describe('LaisserPasserComponent', () => {
  let component: LaisserPasserComponent;
  let fixture: ComponentFixture<LaisserPasserComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LaisserPasserComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LaisserPasserComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
