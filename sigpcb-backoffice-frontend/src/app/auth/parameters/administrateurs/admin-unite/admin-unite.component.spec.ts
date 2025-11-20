import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminUniteComponent } from './admin-unite.component';

describe('AdminUniteComponent', () => {
  let component: AdminUniteComponent;
  let fixture: ComponentFixture<AdminUniteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AdminUniteComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AdminUniteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
