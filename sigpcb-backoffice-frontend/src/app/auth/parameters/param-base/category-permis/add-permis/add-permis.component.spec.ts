import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddPermisComponent } from './add-permis.component';

describe('AddPermisComponent', () => {
  let component: AddPermisComponent;
  let fixture: ComponentFixture<AddPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AddPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
