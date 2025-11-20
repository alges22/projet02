import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { SignaturesRoutingModule } from './signatures-routing.module';
import { SignaturesComponent } from './signatures.component';
import { SignatairesComponent } from './signataires/signataires.component';
import { ActeSignesComponent } from './acte-signes/acte-signes.component';
import { FormsModule } from '@angular/forms';
import { SignatureTopbarComponent } from './components/signature-topbar/signature-topbar.component';
import { CoreModule } from 'src/app/core/core.module';
import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';


@NgModule({
  declarations: [
    SignaturesComponent,
    SignatairesComponent,
    ActeSignesComponent,
    SignatureTopbarComponent
  ],
  imports: [
    CommonModule,
    SignaturesRoutingModule,
    FormsModule,
    CoreModule,
    NgMultiSelectDropDownModule.forRoot()
  ]
})
export class SignaturesModule { }
