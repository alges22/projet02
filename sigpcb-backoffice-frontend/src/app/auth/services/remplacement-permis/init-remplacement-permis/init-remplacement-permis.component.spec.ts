import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InitRemplacementPermisComponent } from './init-remplacement-permis.component';

describe('InitRemplacementPermisComponent', () => {
  let component: InitRemplacementPermisComponent;
  let fixture: ComponentFixture<InitRemplacementPermisComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ InitRemplacementPermisComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InitRemplacementPermisComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
