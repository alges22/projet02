import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ActeSignesComponent } from './acte-signes.component';

describe('ActeSignesComponent', () => {
  let component: ActeSignesComponent;
  let fixture: ComponentFixture<ActeSignesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ActeSignesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ActeSignesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
