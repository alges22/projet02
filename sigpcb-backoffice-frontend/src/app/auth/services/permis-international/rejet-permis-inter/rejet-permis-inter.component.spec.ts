import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RejetPermisInterComponent } from './rejet-permis-inter.component';

describe('RejetPermisInterComponent', () => {
  let component: RejetPermisInterComponent;
  let fixture: ComponentFixture<RejetPermisInterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RejetPermisInterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RejetPermisInterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
