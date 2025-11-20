import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MoniteurComponent } from './moniteur.component';

describe('MoniteurComponent', () => {
  let component: MoniteurComponent;
  let fixture: ComponentFixture<MoniteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MoniteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MoniteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
