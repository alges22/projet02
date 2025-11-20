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

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private cookie: CookieService, private auth: AuthService) {}

  intercept(
    req: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    const lastUsed = this.auth
      .storageService()
      .get<{ timesamps: number }>('last_used');
    if (typeof lastUsed == 'object' && lastUsed != null) {
      const offtime = Date.now() - lastUsed.timesamps;
      console.log("Temps d'inactivité: ", offtime, 'seconde');
    }

    const token = this.cookie.get('access_token');
    let authReq = req;
    if (token) {
      authReq = req.clone({
        headers: req.headers.set('Authorization', `Bearer ${token}`),
      });
      next.handle(authReq);
    }

    return next.handle(authReq).pipe(
      tap(
        (event) => {
          this.auth.storageService().store('last_used', {
            timesamps: Date.now(),
          });
        },
        (error) => {
          this.auth.storageService().store('last_used', {
            timesamps: Date.now(),
          });
          if (error.status == 401) {
            this.auth.logout();
            //Ceci c'est pour checker si l'utilisateur était connecté
            redirectTo('/connexion');
          }
        }
      )
    );
  }
}
