import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FedapayBoxComponent } from './fedapay-box.component';

describe('FedapayBoxComponent', () => {
  let component: FedapayBoxComponent;
  let fixture: ComponentFixture<FedapayBoxComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ FedapayBoxComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(FedapayBoxComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
