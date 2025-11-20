import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MdLoaderComponent } from './md-loader.component';

describe('MdLoaderComponent', () => {
  let component: MdLoaderComponent;
  let fixture: ComponentFixture<MdLoaderComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MdLoaderComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MdLoaderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
