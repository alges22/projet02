import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AeexpiresComponent } from './aeexpires.component';

describe('AeexpiresComponent', () => {
  let component: AeexpiresComponent;
  let fixture: ComponentFixture<AeexpiresComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AeexpiresComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AeexpiresComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
