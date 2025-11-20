import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CandidatParcoursInfoComponent } from './candidat-parcours-info.component';

describe('CandidatParcoursInfoComponent', () => {
  let component: CandidatParcoursInfoComponent;
  let fixture: ComponentFixture<CandidatParcoursInfoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CandidatParcoursInfoComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CandidatParcoursInfoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
