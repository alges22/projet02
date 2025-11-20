import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MoniteurLogoutComponent } from './moniteur-logout.component';

describe('MoniteurLogoutComponent', () => {
  let component: MoniteurLogoutComponent;
  let fixture: ComponentFixture<MoniteurLogoutComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MoniteurLogoutComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MoniteurLogoutComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
