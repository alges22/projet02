import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SubbaremeComponent } from './subbareme.component';

describe('SubbaremeComponent', () => {
  let component: SubbaremeComponent;
  let fixture: ComponentFixture<SubbaremeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SubbaremeComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SubbaremeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
