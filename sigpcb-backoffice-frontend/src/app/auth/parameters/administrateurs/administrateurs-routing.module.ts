import { AdminUniteComponent } from './admin-unite/admin-unite.component';
import { AdminHomeComponent } from './admin-home/admin-home.component';
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdministrateursComponent } from './administrateurs.component';
import { AdminRolesComponent } from './admin-roles/admin-roles.component';
import { AdminTitresComponent } from './admin-titres/admin-titres.component';
import { RoleFormComponent } from './admin-roles/role-form/role-form.component';
import { RoleListComponent } from './admin-roles/role-list/role-list.component';

const routes: Routes = [
  {
    path: '',
    component: AdministrateursComponent,
    children: [
      {
        path: 'administrateurs',
        component: AdminHomeComponent,
      },
      {
        path: 'unite-admins',
        component: AdminUniteComponent,
      },
      {
        path: 'roles',
        component: AdminRolesComponent,
        children: [
          {
            path: '',
            component: RoleListComponent,
          },
          {
            path: 'add',
            component: RoleFormComponent,
          },
          {
            path: 'edit/:id',
            component: RoleFormComponent,
          },
        ],
      },
      {
        path: 'titres',
        component: AdminTitresComponent,
      },
      {
        path: '',
        component: AdminHomeComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AdministrateursRoutingModule {}
