import { ComponentFixture, TestBed } from '@angular/core/testing';

import { JustifRecusComponent } from './justif-recus.component';

describe('JustifRecusComponent', () => {
  let component: JustifRecusComponent;
  let fixture: ComponentFixture<JustifRecusComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ JustifRecusComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(JustifRecusComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
