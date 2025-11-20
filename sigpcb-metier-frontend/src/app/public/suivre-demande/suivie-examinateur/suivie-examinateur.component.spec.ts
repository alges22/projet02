import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuivieExaminateurComponent } from './suivie-examinateur.component';

describe('SuivieExaminateurComponent', () => {
  let component: SuivieExaminateurComponent;
  let fixture: ComponentFixture<SuivieExaminateurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SuivieExaminateurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SuivieExaminateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
