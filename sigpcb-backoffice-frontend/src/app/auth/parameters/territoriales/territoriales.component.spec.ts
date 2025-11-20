import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TerritorialesComponent } from './territoriales.component';

describe('TerritorialesComponent', () => {
  let component: TerritorialesComponent;
  let fixture: ComponentFixture<TerritorialesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TerritorialesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(TerritorialesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
