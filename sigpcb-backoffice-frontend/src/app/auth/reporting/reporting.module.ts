import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ReportingRoutingModule } from './reporting-routing.module';
import { ReportingComponent } from './reporting.component';
import { PaiementsComponent } from './paiements/paiements.component';
import { FormsModule } from '@angular/forms';
import { CoreModule } from 'src/app/core/core.module';
import { PaiementTitresDeviresComponent } from './paiement-titres-devires/paiement-titres-devires.component';

@NgModule({
  declarations: [ReportingComponent, PaiementsComponent, PaiementTitresDeviresComponent],
  imports: [CommonModule, CoreModule, FormsModule, ReportingRoutingModule],
})
export class ReportingModule {}
