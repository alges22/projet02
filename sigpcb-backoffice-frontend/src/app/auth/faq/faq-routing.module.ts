import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { FaqComponent } from './faq.component';
import { FaqHomeComponent } from './faq-home/faq-home.component';

const routes: Routes = [
  {
    path: '',
    component: FaqComponent,
    children: [
      {
        path: '',
        component: FaqHomeComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class FaqRoutingModule {}
