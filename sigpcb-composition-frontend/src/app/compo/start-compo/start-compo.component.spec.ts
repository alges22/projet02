import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StartCompoComponent } from './start-compo.component';

describe('StartCompoComponent', () => {
  let component: StartCompoComponent;
  let fixture: ComponentFixture<StartCompoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ StartCompoComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StartCompoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
