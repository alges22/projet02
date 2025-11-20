import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AutoEcoleListRoutingModule } from './auto-ecole-list-routing.module';
import { AutoEcoleListComponent } from './auto-ecole-list.component';
import { AeactivesComponent } from './aeactives/aeactives.component';
import { AeexpiresComponent } from './aeexpires/aeexpires.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { FormsModule } from '@angular/forms';
import { CoreModule } from 'src/app/core/core.module';
import { AelistsComponent } from './aelists/aelists.component';
import { AutoEcoleFicheComponent } from './components/auto-ecole-fiche/auto-ecole-fiche.component';

@NgModule({
  declarations: [
    AutoEcoleListComponent,
    AeactivesComponent,
    AeexpiresComponent,
    AelistsComponent,
    AutoEcoleFicheComponent,
  ],
  imports: [
    CommonModule,
    AutoEcoleListRoutingModule,
    NgxPaginationModule,
    CoreModule,
    FormsModule,
  ],
})
export class AutoEcoleListModule {}
