import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ListPermisComponent } from './list-permis.component';

describe('ListPermisComponent', () => {
  let component: ListPermisComponent;
  let fixture: ComponentFixture<ListPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ListPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ListPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
