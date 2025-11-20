import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SwitchModalComponent } from './switch-modal.component';

describe('SwitchModalComponent', () => {
  let component: SwitchModalComponent;
  let fixture: ComponentFixture<SwitchModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SwitchModalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SwitchModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
