import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AgregateurComponent } from './agregateur.component';

describe('AgregateurComponent', () => {
  let component: AgregateurComponent;
  let fixture: ComponentFixture<AgregateurComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AgregateurComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AgregateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
