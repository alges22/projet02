import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CandidatHistoricComponent } from './candidat-historic.component';

describe('CandidatHistoricComponent', () => {
  let component: CandidatHistoricComponent;
  let fixture: ComponentFixture<CandidatHistoricComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CandidatHistoricComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CandidatHistoricComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
