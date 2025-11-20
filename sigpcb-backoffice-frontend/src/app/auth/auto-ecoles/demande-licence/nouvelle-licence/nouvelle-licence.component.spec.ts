import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NouvelleLicenceComponent } from './nouvelle-licence.component';

describe('NouvelleLicenceComponent', () => {
  let component: NouvelleLicenceComponent;
  let fixture: ComponentFixture<NouvelleLicenceComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ NouvelleLicenceComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(NouvelleLicenceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
