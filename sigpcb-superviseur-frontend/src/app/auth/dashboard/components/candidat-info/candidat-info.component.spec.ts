import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CandidatInfoComponent } from './candidat-info.component';

describe('CandidatInfoComponent', () => {
  let component: CandidatInfoComponent;
  let fixture: ComponentFixture<CandidatInfoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CandidatInfoComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CandidatInfoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
