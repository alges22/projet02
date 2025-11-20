import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AnattDocConduiteComponent } from './anatt-doc-conduite.component';

describe('AnattDocConduiteComponent', () => {
  let component: AnattDocConduiteComponent;
  let fixture: ComponentFixture<AnattDocConduiteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AnattDocConduiteComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AnattDocConduiteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
