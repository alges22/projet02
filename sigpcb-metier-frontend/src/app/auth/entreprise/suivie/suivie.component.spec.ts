import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuivieComponent } from './suivie.component';

describe('SuivieComponent', () => {
  let component: SuivieComponent;
  let fixture: ComponentFixture<SuivieComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SuivieComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SuivieComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
