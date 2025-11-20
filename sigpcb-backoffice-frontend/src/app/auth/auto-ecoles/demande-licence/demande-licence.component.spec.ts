import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandeLicenceComponent } from './demande-licence.component';

describe('DemandeLicenceComponent', () => {
  let component: DemandeLicenceComponent;
  let fixture: ComponentFixture<DemandeLicenceComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DemandeLicenceComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandeLicenceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
