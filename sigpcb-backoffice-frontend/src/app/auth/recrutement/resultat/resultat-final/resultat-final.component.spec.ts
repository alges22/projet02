import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ResultatFinalComponent } from './resultat-final.component';

describe('ResultatFinalComponent', () => {
  let component: ResultatFinalComponent;
  let fixture: ComponentFixture<ResultatFinalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ResultatFinalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ResultatFinalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
