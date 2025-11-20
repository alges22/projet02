import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ParamLanguesComponent } from './param-langues.component';

describe('ParamLanguesComponent', () => {
  let component: ParamLanguesComponent;
  let fixture: ComponentFixture<ParamLanguesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ParamLanguesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ParamLanguesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
