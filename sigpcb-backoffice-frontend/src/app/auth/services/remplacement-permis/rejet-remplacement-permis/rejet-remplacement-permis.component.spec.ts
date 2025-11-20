import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RejetRemplacementPermisComponent } from './rejet-remplacement-permis.component';

describe('RejetRemplacementPermisComponent', () => {
  let component: RejetRemplacementPermisComponent;
  let fixture: ComponentFixture<RejetRemplacementPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RejetRemplacementPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RejetRemplacementPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
