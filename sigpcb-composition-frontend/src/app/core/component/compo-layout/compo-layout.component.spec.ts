import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CompoLayoutComponent } from './compo-layout.component';

describe('CompoLayoutComponent', () => {
  let component: CompoLayoutComponent;
  let fixture: ComponentFixture<CompoLayoutComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CompoLayoutComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CompoLayoutComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
