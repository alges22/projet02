import { ComponentFixture, TestBed } from '@angular/core/testing';

import { JustifRejetesComponent } from './justif-rejetes.component';

describe('JustifRejetesComponent', () => {
  let component: JustifRejetesComponent;
  let fixture: ComponentFixture<JustifRejetesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ JustifRejetesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(JustifRejetesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
