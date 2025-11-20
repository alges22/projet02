import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ConducteurRoutingModule } from './conducteur-routing.module';
import { ConducteurComponent } from './conducteur.component';
import { EntrepriseConducteurComponent } from './entreprise-conducteur/entreprise-conducteur.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';
import { EntrepriseFicheComponent } from './compoments/entreprise-fiche/entreprise-fiche.component';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [
    ConducteurComponent,
    EntrepriseConducteurComponent,
    EntrepriseFicheComponent,
  ],
  imports: [
    CommonModule,
    ConducteurRoutingModule,
    FormsModule,
    NgxPaginationModule,
    CoreModule,
  ],
})
export class ConducteurModule {}
