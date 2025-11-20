import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FaqRoutingModule } from './faq-routing.module';
import { FaqComponent } from './faq.component';
import { FaqHomeComponent } from './faq-home/faq-home.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [FaqComponent, FaqHomeComponent],
  imports: [
    CommonModule,
    FaqRoutingModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
  ],
})
export class FaqModule {}
