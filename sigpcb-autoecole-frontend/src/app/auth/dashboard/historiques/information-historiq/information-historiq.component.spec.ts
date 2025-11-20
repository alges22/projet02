import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InformationHistoriqComponent } from './information-historiq.component';

describe('InformationHistoriqComponent', () => {
  let component: InformationHistoriqComponent;
  let fixture: ComponentFixture<InformationHistoriqComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ InformationHistoriqComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InformationHistoriqComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
