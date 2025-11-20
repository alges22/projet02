import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InspecteursComponent } from './inspecteurs.component';

describe('InspecteursComponent', () => {
  let component: InspecteursComponent;
  let fixture: ComponentFixture<InspecteursComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ InspecteursComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InspecteursComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
