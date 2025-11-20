import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AnnexeFormComponent } from './annexe-form.component';

describe('AnnexeFormComponent', () => {
  let component: AnnexeFormComponent;
  let fixture: ComponentFixture<AnnexeFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AnnexeFormComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AnnexeFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
