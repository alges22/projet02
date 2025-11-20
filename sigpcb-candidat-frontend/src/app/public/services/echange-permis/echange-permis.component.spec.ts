import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EchangePermisComponent } from './echange-permis.component';

describe('EchangePermisComponent', () => {
  let component: EchangePermisComponent;
  let fixture: ComponentFixture<EchangePermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EchangePermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EchangePermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
