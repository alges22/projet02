import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntrepriseLoginComponent } from './entreprise-login.component';

describe('EntrepriseLoginComponent', () => {
  let component: EntrepriseLoginComponent;
  let fixture: ComponentFixture<EntrepriseLoginComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntrepriseLoginComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntrepriseLoginComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
