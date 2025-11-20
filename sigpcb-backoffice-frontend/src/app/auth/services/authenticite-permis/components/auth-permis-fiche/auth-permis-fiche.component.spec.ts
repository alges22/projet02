import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AuthPermisFicheComponent } from './auth-permis-fiche.component';

describe('AuthPermisFicheComponent', () => {
  let component: AuthPermisFicheComponent;
  let fixture: ComponentFixture<AuthPermisFicheComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AuthPermisFicheComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AuthPermisFicheComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
