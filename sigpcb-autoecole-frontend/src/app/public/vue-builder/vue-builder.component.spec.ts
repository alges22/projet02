import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VueBuilderComponent } from './vue-builder.component';

describe('VueBuilderComponent', () => {
  let component: VueBuilderComponent;
  let fixture: ComponentFixture<VueBuilderComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ VueBuilderComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(VueBuilderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
