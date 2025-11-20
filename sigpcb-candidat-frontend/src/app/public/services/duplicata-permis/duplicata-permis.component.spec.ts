import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DuplicataPermisComponent } from './duplicata-permis.component';

describe('DuplicataPermisComponent', () => {
  let component: DuplicataPermisComponent;
  let fixture: ComponentFixture<DuplicataPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DuplicataPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DuplicataPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
