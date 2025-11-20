import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { StatistiquesRoutingModule } from './statistiques-routing.module';
import { StatistiquesComponent } from './statistiques.component';
import { RapportComponent } from './rapport/rapport.component';
import { FilterSystemComponent } from './filter-system/filter-system.component';
import { FormsModule } from '@angular/forms';
import { CoreModule } from 'src/app/core/core.module';
import { NgChartsModule } from 'ng2-charts';

@NgModule({
  declarations: [
    StatistiquesComponent,
    RapportComponent,
    FilterSystemComponent,
  ],
  imports: [
    CommonModule,
    StatistiquesRoutingModule,
    FormsModule,
    CoreModule,
    NgChartsModule,
  ],
})
export class StatistiquesModule {}
