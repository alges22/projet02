import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { MoniteurRoutingModule } from './moniteur-routing.module';
import { MoniteurComponent } from './moniteur.component';
import { DemandeMoniteurComponent } from './demande-moniteur/demande-moniteur.component';
import { RejetMoniteurComponent } from './rejet-moniteur/rejet-moniteur.component';
import { ValidateMoniteurComponent } from './validate-moniteur/validate-moniteur.component';
import { FormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';
import { CoreModule } from 'src/app/core/core.module';
import { RecrutementMoniteurFicheComponent } from './components/recrutement-moniteur-fiche/recrutement-moniteur-fiche.component';

@NgModule({
  declarations: [
    MoniteurComponent,
    DemandeMoniteurComponent,
    RejetMoniteurComponent,
    ValidateMoniteurComponent,
    RecrutementMoniteurFicheComponent,
  ],
  imports: [
    CommonModule,
    MoniteurRoutingModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
  ],
})
export class MoniteurModule {}
