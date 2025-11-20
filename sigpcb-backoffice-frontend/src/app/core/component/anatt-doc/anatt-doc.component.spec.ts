import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AnattDocComponent } from './anatt-doc.component';

describe('AnattDocComponent', () => {
  let component: AnattDocComponent;
  let fixture: ComponentFixture<AnattDocComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AnattDocComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AnattDocComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
