import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GrDemandesValidateComponent } from './gr-demandes-validate.component';

describe('GrDemandesValidateComponent', () => {
  let component: GrDemandesValidateComponent;
  let fixture: ComponentFixture<GrDemandesValidateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GrDemandesValidateComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GrDemandesValidateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
