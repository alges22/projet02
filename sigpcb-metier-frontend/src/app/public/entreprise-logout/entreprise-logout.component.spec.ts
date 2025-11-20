import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntrepriseLogoutComponent } from './entreprise-logout.component';

describe('EntrepriseLogoutComponent', () => {
  let component: EntrepriseLogoutComponent;
  let fixture: ComponentFixture<EntrepriseLogoutComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntrepriseLogoutComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntrepriseLogoutComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
