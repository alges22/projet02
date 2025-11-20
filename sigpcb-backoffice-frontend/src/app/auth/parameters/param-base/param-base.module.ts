import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ParamBaseRoutingModule } from './param-base-routing.module';
import { ParamBaseComponent } from './param-base.component';
import { ParamLanguesComponent } from './param-langues/param-langues.component';
import { ParamBaseTopbarComponent } from './components/param-base-topbar/param-base-topbar.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';
import { AgregateurComponent } from './agregateur/agregateur.component';
import { CategoryPermisComponent } from './category-permis/category-permis.component';
import { NgWindowModule } from 'src/app/ng-window/ng-window.module';
import { AddPermisComponent } from './category-permis/add-permis/add-permis.component';
import { ListPermisComponent } from './category-permis/list-permis/list-permis.component';
import { ShowCategoryComponent } from './category-permis/show-category/show-category.component';
import { RestrictionComponent } from './restriction/restriction.component';
import { ParamConfigComponent } from './param-config/param-config.component';

@NgModule({
  declarations: [
    ParamBaseComponent,
    ParamLanguesComponent,
    ParamBaseTopbarComponent,
    AgregateurComponent,
    CategoryPermisComponent,
    AddPermisComponent,
    ListPermisComponent,
    ShowCategoryComponent,
    RestrictionComponent,
    ParamConfigComponent,
  ],
  imports: [
    CommonModule,
    ParamBaseRoutingModule,
    FormsModule,
    CoreModule,
    NgWindowModule,
  ],
})
export class ParamBaseModule {}
