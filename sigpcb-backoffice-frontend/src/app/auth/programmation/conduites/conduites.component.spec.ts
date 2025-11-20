import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ConduitesComponent } from './conduites.component';

describe('ConduitesComponent', () => {
  let component: ConduitesComponent;
  let fixture: ComponentFixture<ConduitesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ConduitesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ConduitesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
