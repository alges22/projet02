import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuivreMoniteurComponent } from './suivre-moniteur.component';

describe('SuivreMoniteurComponent', () => {
  let component: SuivreMoniteurComponent;
  let fixture: ComponentFixture<SuivreMoniteurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SuivreMoniteurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SuivreMoniteurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
