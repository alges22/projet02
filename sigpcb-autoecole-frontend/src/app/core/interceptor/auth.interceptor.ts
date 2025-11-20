import { Injectable } from '@angular/core';
import {
  HttpInterceptor,
  HttpHandler,
  HttpRequest,
  HttpEvent,
} from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { CookieService } from 'ngx-cookie';
import { AuthService } from '../services/auth.service';
import { redirectTo } from 'src/app/helpers/helpers';
import { Ae } from '../interfaces/user.interface';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private cookie: CookieService, private auth: AuthService) {}

  intercept(
    req: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    const token = this.cookie.get('access_token');
    let authReq = req;
    if (token) {
      const ae = this.auth.storageService().get<Ae>('ae');
      let sett = req.headers.set('Authorization', `Bearer ${token}`);
      if (ae) {
        sett = sett.set('X-Ae', ae.auto_ecole_id.toString());
      }
      authReq = req.clone({
        headers: sett,
      });
      next.handle(authReq);
    }

    return next.handle(authReq).pipe(
      tap(
        (event) => {},
        (error) => {
          if (error.status == 401) {
            this.auth.logout();
            redirectTo('/connexion');
          }
        }
      )
    );
  }
}
