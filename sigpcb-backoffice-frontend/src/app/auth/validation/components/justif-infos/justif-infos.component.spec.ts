import { ComponentFixture, TestBed } from '@angular/core/testing';

import { JustifInfosComponent } from './justif-infos.component';

describe('JustifInfosComponent', () => {
  let component: JustifInfosComponent;
  let fixture: ComponentFixture<JustifInfosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ JustifInfosComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(JustifInfosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
