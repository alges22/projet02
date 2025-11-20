import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ProrogationPermisComponent } from './prorogation-permis.component';

describe('ProrogationPermisComponent', () => {
  let component: ProrogationPermisComponent;
  let fixture: ComponentFixture<ProrogationPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ProrogationPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ProrogationPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
