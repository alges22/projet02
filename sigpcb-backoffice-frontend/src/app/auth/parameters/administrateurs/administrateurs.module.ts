import { CoreModule } from './../../../core/core.module';
import { FormsModule } from '@angular/forms';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AdministrateursRoutingModule } from './administrateurs-routing.module';
import { AdministrateursComponent } from './administrateurs.component';
import { AdminTopbarComponent } from './components/admin-topbar/admin-topbar.component';
import { AdminRolesComponent } from './admin-roles/admin-roles.component';
import { AdminHomeComponent } from './admin-home/admin-home.component';
import { AdminUniteComponent } from './admin-unite/admin-unite.component';
import { AdminTitresComponent } from './admin-titres/admin-titres.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { RoleFormComponent } from './admin-roles/role-form/role-form.component';
import { RoleListComponent } from './admin-roles/role-list/role-list.component';

@NgModule({
  declarations: [
    AdministrateursComponent,
    AdminHomeComponent,
    AdminUniteComponent,
    AdminTopbarComponent,
    AdminRolesComponent,
    AdminTitresComponent,
    RoleFormComponent,
    RoleListComponent
  ],
  imports: [
    CommonModule,
    AdministrateursRoutingModule,
    FormsModule,
    NgxPaginationModule,
    CoreModule,
  ],
})
export class AdministrateursModule {}
