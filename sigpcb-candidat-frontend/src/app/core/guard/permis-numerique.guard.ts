import { Injectable } from '@angular/core';
import {
  ActivatedRouteSnapshot,
  CanActivate,
  CanActivateChild,
  Router,
  RouterStateSnapshot,
  UrlTree,
} from '@angular/router';
import { Observable } from 'rxjs';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root',
})
export class PermisNumeriqueGuard implements CanActivate, CanActivateChild {
  constructor(private router: Router, private authService: AuthService) {}
  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ):
    | Observable<boolean | UrlTree>
    | Promise<boolean | UrlTree>
    | boolean
    | UrlTree {
    const expectedSlug = route.data['allowedSlug'];
    if (route.params['slug'] === expectedSlug) {
      return this.hasPermis(state);
    } else {
      return true;
    }
    // return this.hasPermis(state);
  }
  canActivateChild(
    childRoute: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ):
    | Observable<boolean | UrlTree>
    | Promise<boolean | UrlTree>
    | boolean
    | UrlTree {
    return this.hasPermis(state);
  }

  hasPermis(state: RouterStateSnapshot) {
    const hasPermis = this.authService.checkedUserHasPermis();
    console.log(hasPermis);
    if (!hasPermis) {
      this.router.navigate(['/dashboard']);
      return false;
    }
    return hasPermis;
  }
}
