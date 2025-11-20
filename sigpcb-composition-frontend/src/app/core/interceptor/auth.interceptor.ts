import { Injectable } from '@angular/core';
import {
  HttpInterceptor,
  HttpHandler,
  HttpRequest,
  HttpEvent,
} from '@angular/common/http';
import { Observable, finalize, tap } from 'rxjs';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';
import { redirectTo, refresh } from 'src/app/helpers/helpers';
import { AlertService } from '../services/alert.service';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(
    private storage: StorageService,
    private auth: AuthService,
    private alertService: AlertService
  ) {}

  intercept(
    req: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    let token = this.storage.get('access_token');
    let authReq = req;

    if (token) {
      authReq = req.clone({
        headers: req.headers.set('Authorization', `Bearer ${token}`),
      });
      next.handle(authReq);
    }

    return next.handle(authReq).pipe(
      tap(
        (event) => {},
        (error) => {
          this.alertService.alert(error.message);
          if (error.status == 401) {
            this.auth.logout();
            //Ceci c'est pour checker si l'utilisateur était connecté
            redirectTo('/login');
          }

          let message = error?.error?.message || 'Un problème est survenu';

          if (!navigator.onLine) {
            this.alertService.alert('Vous avez perdu le réseau', 'danger');
          } else {
            this.alertService.alert(message, 'danger', () => {
              refresh();
            });
          }
        }
      ),
      finalize(() => {})
    );
  }
}
