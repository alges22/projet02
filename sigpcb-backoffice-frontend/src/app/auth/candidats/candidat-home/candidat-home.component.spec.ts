import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CandidatHomeComponent } from './candidat-home.component';

describe('CandidatHomeComponent', () => {
  let component: CandidatHomeComponent;
  let fixture: ComponentFixture<CandidatHomeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CandidatHomeComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CandidatHomeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
