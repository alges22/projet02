import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ValidationRoutingModule } from './validation-routing.module';
import { ValidationComponent } from './validation.component';
import { JustifRecusComponent } from './justifs/justif-recus/justif-recus.component';
import { JustifRejetesComponent } from './justifs/justif-rejetes/justif-rejetes.component';
import { JustifValidesComponent } from './justifs/justif-valides/justif-valides.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';
import { JustifInfosComponent } from './components/justif-infos/justif-infos.component';

@NgModule({
  declarations: [
    ValidationComponent,
    JustifRecusComponent,
    JustifRejetesComponent,
    JustifValidesComponent,
    JustifInfosComponent,
  ],
  imports: [
    CommonModule,
    CoreModule,
    FormsModule,
    NgxPaginationModule,
    ValidationRoutingModule,
  ],
})
export class ValidationModule {}
