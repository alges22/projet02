import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AeactivesComponent } from './aeactives.component';

describe('AeactivesComponent', () => {
  let component: AeactivesComponent;
  let fixture: ComponentFixture<AeactivesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AeactivesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AeactivesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
