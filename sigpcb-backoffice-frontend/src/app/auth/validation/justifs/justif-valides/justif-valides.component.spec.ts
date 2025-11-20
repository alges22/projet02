import { ComponentFixture, TestBed } from '@angular/core/testing';

import { JustifValidesComponent } from './justif-valides.component';

describe('JustifValidesComponent', () => {
  let component: JustifValidesComponent;
  let fixture: ComponentFixture<JustifValidesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ JustifValidesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(JustifValidesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
