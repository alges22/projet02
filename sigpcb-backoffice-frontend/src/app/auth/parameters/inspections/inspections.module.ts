import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { InspectionsRoutingModule } from './inspections-routing.module';
import { InspectionsComponent } from './inspections.component';
import { InspecteursComponent } from './inspecteurs/inspecteurs.component';
import { FormsModule } from '@angular/forms';
import { CoreModule } from 'src/app/core/core.module';
import { NgWindowModule } from 'src/app/ng-window/ng-window.module';
import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';
import { ExaminateursComponent } from './examinateurs/examinateurs.component';

@NgModule({
  declarations: [InspectionsComponent, InspecteursComponent, ExaminateursComponent],
  imports: [
    CommonModule,
    InspectionsRoutingModule,
    FormsModule,
    CoreModule,
    NgWindowModule,
    NgMultiSelectDropDownModule.forRoot(),
  ],
})
export class InspectionsModule {}
