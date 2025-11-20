import { GuestGuard } from './core/guard/guest.guard';
import { HomeComponent } from './public/home/home.component';
import { LogoutComponent } from './public/logout/logout.component';
import { LoginComponent } from './public/login/login.component';
import { PublicComponent } from './public/public.component';
import { AuthGuard } from './core/guard/auth.guard';
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ForgotPasswordComponent } from './public/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './public/reset-password/reset-password.component';

const routes: Routes = [
  {
    path: '',
    component: PublicComponent,
    children: [
      {
        path: 'connexion',
        canActivate: [GuestGuard],
        component: LoginComponent,
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
        path: '',
        canActivate: [AuthGuard],
        component: HomeComponent,
      },
    ],
  },
  {
    path: '',
    canActivateChild: [AuthGuard],
    loadChildren: () => import('./auth/auth.module').then((m) => m.AuthModule),
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
