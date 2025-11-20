import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminTitresComponent } from './admin-titres.component';

describe('AdminTitresComponent', () => {
  let component: AdminTitresComponent;
  let fixture: ComponentFixture<AdminTitresComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AdminTitresComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AdminTitresComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
