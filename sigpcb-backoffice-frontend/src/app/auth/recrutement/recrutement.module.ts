import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { RecrutementRoutingModule } from './recrutement-routing.module';
import { RecrutementComponent } from './recrutement.component';


@NgModule({
  declarations: [
    RecrutementComponent
  ],
  imports: [
    CommonModule,
    RecrutementRoutingModule
  ]
})
export class RecrutementModule { }
