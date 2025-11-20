import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AutoEcolesComponent } from './auto-ecoles.component';

describe('AutoEcolesComponent', () => {
  let component: AutoEcolesComponent;
  let fixture: ComponentFixture<AutoEcolesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AutoEcolesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AutoEcolesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
