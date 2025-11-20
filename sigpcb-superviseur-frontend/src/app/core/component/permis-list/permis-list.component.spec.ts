import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PermisListComponent } from './permis-list.component';

describe('PermisListComponent', () => {
  let component: PermisListComponent;
  let fixture: ComponentFixture<PermisListComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PermisListComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PermisListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
