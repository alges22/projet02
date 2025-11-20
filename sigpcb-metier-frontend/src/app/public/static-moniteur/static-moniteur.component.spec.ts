import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StaticMoniteurComponent } from './static-moniteur.component';

describe('StaticMoniteurComponent', () => {
  let component: StaticMoniteurComponent;
  let fixture: ComponentFixture<StaticMoniteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ StaticMoniteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StaticMoniteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
