import { FormsModule } from '@angular/forms';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { GestionRoutingModule } from './gestion-routing.module';
import { GestionComponent } from './gestion.component';
import { CoreModule } from 'src/app/core/core.module';
import { GestDashboardComponent } from './gest-dashboard/gest-dashboard.component';
import { MonitoringComponent } from './monitoring/monitoring.component';
import { CandidatFormationComponent } from './candidat-formation/candidat-formation.component';
import { AgendaComponent } from './agenda/agenda.component';
import { ServiceClientComponent } from './service-client/service-client.component';
import { FaqComponent } from './faq/faq.component';
import { StatutValidationComponent } from './statut-validation/statut-validation.component';
import { CandidatsComponent } from './candidats/candidats.component';
import { AbsencesComponent } from './absences/absences.component';
import { DossierRejetesComponent } from './statut-validation/dossier-rejetes/dossier-rejetes.component';
import { DossierValidesComponent } from './statut-validation/dossier-valides/dossier-valides.component';
import { DossierPendingComponent } from './statut-validation/dossier-pending/dossier-pending.component';
import { DossierInitComponent } from './candidat-formation/dossier-init/dossier-init.component';
import { AcheveNonPresentesComponent } from './candidat-formation/acheve-non-presentes/acheve-non-presentes.component';
import { NgxPaginationModule } from 'ngx-pagination';

@NgModule({
  declarations: [
    GestionComponent,
    GestDashboardComponent,
    MonitoringComponent,
    CandidatFormationComponent,
    AgendaComponent,
    ServiceClientComponent,
    FaqComponent,
    StatutValidationComponent,
    CandidatsComponent,
    AbsencesComponent,
    DossierRejetesComponent,
    DossierValidesComponent,
    DossierPendingComponent,
    DossierInitComponent,
    AcheveNonPresentesComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    CoreModule,
    NgxPaginationModule,
    GestionRoutingModule,
  ],
  exports: [CandidatFormationComponent, StatutValidationComponent],
})
export class GestionModule {}
