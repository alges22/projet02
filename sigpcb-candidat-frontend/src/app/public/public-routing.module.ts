import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GuestGuard } from '../core/guard/guest.guard';
import { LoginComponent } from './login/login.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { LogoutComponent } from './logout/logout.component';
import { RegisterComponent } from './register/register.component';
import { VueBuilderComponent } from './vue-builder/vue-builder.component';
import { ServicesComponent } from './services/services.component';
import { PermisNumeriqueGuard } from '../core/guard/permis-numerique.guard';

const routes: Routes = [
  {
    path: '',
    loadChildren: () =>
      import('./static/static.module').then((m) => m.StaticModuleModule),
  },
  {
    path: 'connexion',
    canActivate: [GuestGuard],
    component: LoginComponent,
  },
  {
    path: 'inscription',
    canActivate: [GuestGuard],
    component: RegisterComponent,
  },
  {
    path: 'forgot-password',
    canActivate: [GuestGuard],
    component: ForgotPasswordComponent,
  },
  {
    path: 'reset-password',
    canActivate: [GuestGuard],
    component: ResetPasswordComponent,
  },
  {
    path: 'logout',
    component: LogoutComponent,
  },
  {
    path: 'services/:slug',
    canActivate: [PermisNumeriqueGuard],
    component: ServicesComponent,
    // data: { allowedSlug: 'demande-permis-numerique' },
  },
  {
    path: 'services/:slug/rejets/:rejetId',
    canActivate: [PermisNumeriqueGuard],
    component: ServicesComponent,
    // data: { allowedSlug: 'demande-permis-numerique' },
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PublicRoutingModule {}
