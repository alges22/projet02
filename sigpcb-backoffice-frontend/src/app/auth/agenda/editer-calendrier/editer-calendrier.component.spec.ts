import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EditerCalendrierComponent } from './editer-calendrier.component';

describe('EditerCalendrierComponent', () => {
  let component: EditerCalendrierComponent;
  let fixture: ComponentFixture<EditerCalendrierComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EditerCalendrierComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EditerCalendrierComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
