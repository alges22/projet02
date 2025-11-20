import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidateAuthPermisComponent } from './validate-auth-permis.component';

describe('ValidateAuthPermisComponent', () => {
  let component: ValidateAuthPermisComponent;
  let fixture: ComponentFixture<ValidateAuthPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ValidateAuthPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ValidateAuthPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
