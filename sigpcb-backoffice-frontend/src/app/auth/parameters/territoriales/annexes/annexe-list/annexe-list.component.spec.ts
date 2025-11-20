import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AnnexeListComponent } from './annexe-list.component';

describe('AnnexeListComponent', () => {
  let component: AnnexeListComponent;
  let fixture: ComponentFixture<AnnexeListComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AnnexeListComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AnnexeListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
