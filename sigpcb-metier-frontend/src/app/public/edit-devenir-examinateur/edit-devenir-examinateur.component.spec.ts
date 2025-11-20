import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EditDevenirExaminateurComponent } from './edit-devenir-examinateur.component';

describe('EditDevenirExaminateurComponent', () => {
  let component: EditDevenirExaminateurComponent;
  let fixture: ComponentFixture<EditDevenirExaminateurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EditDevenirExaminateurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EditDevenirExaminateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
