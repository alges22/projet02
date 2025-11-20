import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LaisserPasserHomeComponent } from './laisser-passer-home.component';

describe('LaisserPasserHomeComponent', () => {
  let component: LaisserPasserHomeComponent;
  let fixture: ComponentFixture<LaisserPasserHomeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LaisserPasserHomeComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LaisserPasserHomeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
