import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RejetEchangePermisComponent } from './rejet-echange-permis.component';

describe('RejetEchangePermisComponent', () => {
  let component: RejetEchangePermisComponent;
  let fixture: ComponentFixture<RejetEchangePermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RejetEchangePermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RejetEchangePermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
