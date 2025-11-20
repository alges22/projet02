import { NgxPaginationModule } from 'ngx-pagination';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TerritorialesRoutingModule } from './territoriales-routing.module';
import { TerritorialesComponent } from './territoriales.component';
import { AnnexesComponent } from './annexes/annexes.component';
import { CoreModule } from '../../../core/core.module';
import { TerritorialTopbarComponent } from './components/territorial-topbar/territorial-topbar.component';
import { DepartementsComponent } from './departements/departements.component';
import { CommunesComponent } from './communes/communes.component';
import { ArrondissementsComponent } from './arrondissements/arrondissements.component';
import { FormsModule } from '@angular/forms';
import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';
import { AnnexeListComponent } from './annexes/annexe-list/annexe-list.component';
import { AnnexeFormComponent } from './annexes/annexe-form/annexe-form.component';
import { NgWindowModule } from 'src/app/ng-window/ng-window.module';

@NgModule({
  declarations: [
    TerritorialesComponent,
    AnnexesComponent,
    TerritorialTopbarComponent,
    DepartementsComponent,
    CommunesComponent,
    ArrondissementsComponent,
    AnnexeListComponent,
    AnnexeFormComponent,
  ],
  imports: [
    CommonModule,
    TerritorialesRoutingModule,
    FormsModule,
    CoreModule,
    NgxPaginationModule,
    NgMultiSelectDropDownModule.forRoot(),
    NgWindowModule
  ],
})
export class TerritorialesModule {}
