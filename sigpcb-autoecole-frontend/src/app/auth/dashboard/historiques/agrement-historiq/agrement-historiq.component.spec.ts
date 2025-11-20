import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AgrementHistoriqComponent } from './agrement-historiq.component';

describe('AgrementHistoriqComponent', () => {
  let component: AgrementHistoriqComponent;
  let fixture: ComponentFixture<AgrementHistoriqComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AgrementHistoriqComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AgrementHistoriqComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
