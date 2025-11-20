import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntrepriseConducteurComponent } from './entreprise-conducteur.component';

describe('EntrepriseConducteurComponent', () => {
  let component: EntrepriseConducteurComponent;
  let fixture: ComponentFixture<EntrepriseConducteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntrepriseConducteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntrepriseConducteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
