import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NgWindowFooterComponent } from './ng-window-footer.component';

describe('NgWindowFooterComponent', () => {
  let component: NgWindowFooterComponent;
  let fixture: ComponentFixture<NgWindowFooterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ NgWindowFooterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NgWindowFooterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
