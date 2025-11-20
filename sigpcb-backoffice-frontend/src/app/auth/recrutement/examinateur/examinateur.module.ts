import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ExaminateurRoutingModule } from './examinateur-routing.module';
import { ExaminateurComponent } from './examinateur.component';
import { CoreModule } from 'src/app/core/core.module';
import { DemandeExaminateurComponent } from './demande-examinateur/demande-examinateur.component';
import { FormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';
import { RecrutementExaminateurFicheComponent } from './components/recrutement-examinateur-fiche/recrutement-examinateur-fiche.component';
import { RejetExaminateurComponent } from './rejet-examinateur/rejet-examinateur.component';
import { ValidateExaminateurComponent } from './validate-examinateur/validate-examinateur.component';

@NgModule({
  declarations: [ExaminateurComponent, DemandeExaminateurComponent, RecrutementExaminateurFicheComponent, RejetExaminateurComponent, ValidateExaminateurComponent],
  imports: [
    CommonModule,
    ExaminateurRoutingModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
  ],
})
export class ExaminateurModule {}
