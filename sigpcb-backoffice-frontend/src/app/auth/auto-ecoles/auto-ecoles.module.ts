import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AutoEcolesRoutingModule } from './auto-ecoles-routing.module';
import { AutoEcolesComponent } from './auto-ecoles.component';
import { CoreModule } from 'src/app/core/core.module';
import { NgxPaginationModule } from 'ngx-pagination';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [AutoEcolesComponent],
  imports: [
    CommonModule,
    CoreModule,
    NgxPaginationModule,
    FormsModule,
    AutoEcolesRoutingModule,
  ],
})
export class AutoEcolesModule {}
