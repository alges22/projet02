import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RemplacementPermisComponent } from './remplacement-permis.component';

describe('RemplacementPermisComponent', () => {
  let component: RemplacementPermisComponent;
  let fixture: ComponentFixture<RemplacementPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RemplacementPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RemplacementPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
