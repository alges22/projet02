import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ParamBaseComponent } from './param-base.component';
import { ParamLanguesComponent } from './param-langues/param-langues.component';
import { CategoryPermisComponent } from './category-permis/category-permis.component';
import { ListPermisComponent } from './category-permis/list-permis/list-permis.component';
import { AddPermisComponent } from './category-permis/add-permis/add-permis.component';
import { ShowCategoryComponent } from './category-permis/show-category/show-category.component';
import { RestrictionComponent } from './restriction/restriction.component';
import { ParamConfigComponent } from './param-config/param-config.component';

const routes: Routes = [
  {
    path: '',
    component: ParamBaseComponent,
    children: [
      {
        path: 'langues',
        component: ParamLanguesComponent,
      },

      {
        path: 'categorie-permis',
        component: CategoryPermisComponent,
        children: [
          {
            path: '',
            component: ListPermisComponent,
          },
          {
            path: 'add',
            component: AddPermisComponent,
          },
          {
            path: 'edit/:id',
            component: AddPermisComponent,
          },
          {
            path: 'show/:id',
            component: ShowCategoryComponent,
          },
        ],
      },
      {
        path: 'restrictions',
        component: RestrictionComponent,
      },
      {
        path: 'configuration',
        component: ParamConfigComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ParamBaseRoutingModule {}
