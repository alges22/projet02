import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { LaisserPasserRoutingModule } from './laisser-passer-routing.module';
import { LaisserPasserComponent } from './laisser-passer.component';
import { LaisserPasserHomeComponent } from './laisser-passer-home/laisser-passer-home.component';
import { FormsModule } from '@angular/forms';
import { CoreModule } from 'src/app/core/core.module';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [LaisserPasserComponent, LaisserPasserHomeComponent],
  imports: [
    CommonModule,
    LaisserPasserRoutingModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
  ],
})
export class LaisserPasserModule {}
