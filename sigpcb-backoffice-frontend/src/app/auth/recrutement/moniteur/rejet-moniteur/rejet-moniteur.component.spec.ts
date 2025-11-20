import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RejetMoniteurComponent } from './rejet-moniteur.component';

describe('RejetMoniteurComponent', () => {
  let component: RejetMoniteurComponent;
  let fixture: ComponentFixture<RejetMoniteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RejetMoniteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RejetMoniteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
