import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SignatairesComponent } from './signataires.component';

describe('SignatairesComponent', () => {
  let component: SignatairesComponent;
  let fixture: ComponentFixture<SignatairesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SignatairesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SignatairesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
