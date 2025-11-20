import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdmisDefinitifsComponent } from './admis-definitifs.component';

describe('AdmisDefinitifsComponent', () => {
  let component: AdmisDefinitifsComponent;
  let fixture: ComponentFixture<AdmisDefinitifsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AdmisDefinitifsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AdmisDefinitifsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
