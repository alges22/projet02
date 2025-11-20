import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RejetProrogationComponent } from './rejet-prorogation.component';

describe('RejetProrogationComponent', () => {
  let component: RejetProrogationComponent;
  let fixture: ComponentFixture<RejetProrogationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RejetProrogationComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RejetProrogationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
