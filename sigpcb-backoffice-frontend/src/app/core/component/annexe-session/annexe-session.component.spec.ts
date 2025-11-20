import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AnnexeSessionComponent } from './annexe-session.component';

describe('AnnexeSessionComponent', () => {
  let component: AnnexeSessionComponent;
  let fixture: ComponentFixture<AnnexeSessionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AnnexeSessionComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AnnexeSessionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
