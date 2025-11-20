import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RejetAuthPermisComponent } from './rejet-auth-permis.component';

describe('RejetAuthPermisComponent', () => {
  let component: RejetAuthPermisComponent;
  let fixture: ComponentFixture<RejetAuthPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RejetAuthPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RejetAuthPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
