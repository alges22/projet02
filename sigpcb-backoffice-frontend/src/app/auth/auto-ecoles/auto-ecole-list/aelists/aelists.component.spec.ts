import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AelistsComponent } from './aelists.component';

describe('AelistsComponent', () => {
  let component: AelistsComponent;
  let fixture: ComponentFixture<AelistsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AelistsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AelistsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
