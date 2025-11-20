import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PermisNumeriqueComponent } from './permis-numerique.component';

describe('PermisNumeriqueComponent', () => {
  let component: PermisNumeriqueComponent;
  let fixture: ComponentFixture<PermisNumeriqueComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PermisNumeriqueComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PermisNumeriqueComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
