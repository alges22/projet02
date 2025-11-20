import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BaremeConduitesComponent } from './bareme-conduites.component';

describe('BaremeConduitesComponent', () => {
  let component: BaremeConduitesComponent;
  let fixture: ComponentFixture<BaremeConduitesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BaremeConduitesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BaremeConduitesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
