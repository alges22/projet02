import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AuthenticitePermisComponent } from './authenticite-permis.component';

describe('AuthenticitePermisComponent', () => {
  let component: AuthenticitePermisComponent;
  let fixture: ComponentFixture<AuthenticitePermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AuthenticitePermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AuthenticitePermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
