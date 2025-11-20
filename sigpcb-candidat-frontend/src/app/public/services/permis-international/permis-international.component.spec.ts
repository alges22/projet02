import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PermisInternationalComponent } from './permis-international.component';

describe('PermisInternationalComponent', () => {
  let component: PermisInternationalComponent;
  let fixture: ComponentFixture<PermisInternationalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PermisInternationalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PermisInternationalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
