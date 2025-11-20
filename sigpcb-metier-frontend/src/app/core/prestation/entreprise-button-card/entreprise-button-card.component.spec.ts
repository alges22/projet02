import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntrepriseButtonCardComponent } from './entreprise-button-card.component';

describe('EntrepriseButtonCardComponent', () => {
  let component: EntrepriseButtonCardComponent;
  let fixture: ComponentFixture<EntrepriseButtonCardComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntrepriseButtonCardComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntrepriseButtonCardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
