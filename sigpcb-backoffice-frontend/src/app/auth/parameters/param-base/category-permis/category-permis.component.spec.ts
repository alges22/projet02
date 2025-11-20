import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CategoryPermisComponent } from './category-permis.component';

describe('CategoryPermisComponent', () => {
  let component: CategoryPermisComponent;
  let fixture: ComponentFixture<CategoryPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CategoryPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CategoryPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
