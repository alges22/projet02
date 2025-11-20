import { CoreModule } from './../../core/core.module';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ParametersRoutingModule } from './parameters-routing.module';
import { ParametersComponent } from './parameters.component';
import { ParametersHomeComponent } from './parameters-home/parameters-home.component';

@NgModule({
  declarations: [ParametersComponent, ParametersHomeComponent],
  imports: [CommonModule, CoreModule, ParametersRoutingModule],
})
export class ParametersModule {}
