import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InscriptionConduiteComponent } from './inscription-conduite.component';

describe('InscriptionConduiteComponent', () => {
  let component: InscriptionConduiteComponent;
  let fixture: ComponentFixture<InscriptionConduiteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ InscriptionConduiteComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InscriptionConduiteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
