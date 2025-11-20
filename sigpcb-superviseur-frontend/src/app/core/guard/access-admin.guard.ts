import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot, UrlTree } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AccessAdminGuard implements CanActivate {
  constructor(private router: Router, private authService: AuthService) {}
  canActivate(
  next: ActivatedRouteSnapshot,
  state: RouterStateSnapshot): boolean {
    const userPermissions = JSON.parse(JSON.stringify(this.authService.storageService().get('userPermissions'))); // Récupération des rôles de l'utilisateur depuis le localstorage
    if (userPermissions) { // Vérification si l'utilisateur a le rôle "admin"
      if (userPermissions.some((permission:any) => permission.name === 'r-admin')) { // Vérification si l'utilisateur a le rôle "admin"
      const allowedRoutes = ['home']; // Routes accessibles pour l'utilisateur ayant le rôle "admin"
      const requestedRoute = next.routeConfig ? next.routeConfig.path : null; //Récupération de la route demandée et Vérification si la propriété routeConfig est null
      if (requestedRoute && allowedRoutes.includes(requestedRoute)) { // Vérification si la route demandée est accessible
        return true;
      }else {
          // Rediriger vers la page d'accueil si l'utilisateur tente d'accéder à "administrateurs/home"
          this.router.parseUrl('/dashboard');
        }
    }

    // if (userPermissions.some((role:any) => role.name === 'super-admin')) {
    //   const allowedRoutes = ['dashboard','home','titres','unite-admins','roles'];
    //   const requestedRoute = next.routeConfig ? next.routeConfig.path : null;
    //   if (requestedRoute && allowedRoutes.includes(requestedRoute)) {
    //     return true;
    //   }
    // }
  }


    this.router.navigate(['/dashboard']);
    return false;
  }

}
