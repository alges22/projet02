import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AutoEcoleListComponent } from './auto-ecole-list.component';

describe('AutoEcoleListComponent', () => {
  let component: AutoEcoleListComponent;
  let fixture: ComponentFixture<AutoEcoleListComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AutoEcoleListComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AutoEcoleListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
