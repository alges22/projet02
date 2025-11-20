import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidateRemplacementPermisComponent } from './validate-remplacement-permis.component';

describe('ValidateRemplacementPermisComponent', () => {
  let component: ValidateRemplacementPermisComponent;
  let fixture: ComponentFixture<ValidateRemplacementPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ValidateRemplacementPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ValidateRemplacementPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
