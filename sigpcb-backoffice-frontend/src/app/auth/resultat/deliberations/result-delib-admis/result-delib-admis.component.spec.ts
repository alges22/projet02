import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ResultDelibAdmisComponent } from './result-delib-admis.component';

describe('ResultDelibAdmisComponent', () => {
  let component: ResultDelibAdmisComponent;
  let fixture: ComponentFixture<ResultDelibAdmisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ResultDelibAdmisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ResultDelibAdmisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
