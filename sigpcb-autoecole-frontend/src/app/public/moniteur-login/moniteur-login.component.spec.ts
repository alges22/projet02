import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MoniteurLoginComponent } from './moniteur-login.component';

describe('MoniteurLoginComponent', () => {
  let component: MoniteurLoginComponent;
  let fixture: ComponentFixture<MoniteurLoginComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MoniteurLoginComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MoniteurLoginComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
