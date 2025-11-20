import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TerritorialTopbarComponent } from './territorial-topbar.component';

describe('TerritorialTopbarComponent', () => {
  let component: TerritorialTopbarComponent;
  let fixture: ComponentFixture<TerritorialTopbarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TerritorialTopbarComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(TerritorialTopbarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
