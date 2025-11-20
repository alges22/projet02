import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RejetExaminateurComponent } from './rejet-examinateur.component';

describe('RejetExaminateurComponent', () => {
  let component: RejetExaminateurComponent;
  let fixture: ComponentFixture<RejetExaminateurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RejetExaminateurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RejetExaminateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
