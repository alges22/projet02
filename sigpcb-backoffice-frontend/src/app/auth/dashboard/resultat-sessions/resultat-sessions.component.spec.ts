import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ResultatSessionsComponent } from './resultat-sessions.component';

describe('ResultatSessionsComponent', () => {
  let component: ResultatSessionsComponent;
  let fixture: ComponentFixture<ResultatSessionsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ResultatSessionsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ResultatSessionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
