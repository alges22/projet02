import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CookieService } from 'ngx-cookie';
import { Observable } from 'rxjs';
import { apiUrl, deleteCookie, is_string } from 'src/app/helpers/helpers';
import { StorageService } from './storage.service';
import { RespType } from 'src/app/types/RespType';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  static EXP_DAYS = 30;
  static REDIRECTTO = '/dashboard';

  constructor(
    private http: HttpClient,
    private cookie: CookieService,
    private storage: StorageService
  ) {}

  signin(param: any): Observable<any> {
    return this.http.post<any>(apiUrl('/login'), param);
  }

  signup(param: any): Observable<ServerResponseType> {
    return this.http.post<ServerResponseType>(apiUrl('/register'), param);
  }
  checked() {
    const access_token = this.cookie.get('access_token');
    if (!access_token) {
      return false;
    }
    return is_string(access_token) && access_token.length > 1;
  }

  attempt(access_token: string) {
    const now = new Date();
    now.setDate(now.getDate() + AuthService.EXP_DAYS);
    this.cookie.put('access_token', access_token, {
      expires: now,
    });
  }
  logout() {
    this.storage.remove('auth');
    deleteCookie('access_token');
  }

  auth() {
    if (this.checked()) {
      return this.storage.get<any>('auth');
    }

    return null;
  }
  storageService() {
    return this.storage;
  }

  profile() {
    return this.http.get<RespType>(apiUrl('/users/profiles'));
  }

  opt(param: any) {
    return this.http.post<any>(apiUrl('/login/verify-otp'), param);
  }

  /**
   * Renvoie Opt
   * @param param
   * @returns
   */
  resendOpt(param: any) {
    return this.http.post<any>(apiUrl('/login/resend-code'), param);
  }
  /**
   *  Mot de passe oublié
   * @param email
   * @param url
   * @returns
   */
  forgotPassword(email: string, url: string) {
    return this.http.post<any>(apiUrl('/login/forgot-password'), {
      email: email,
      url: url,
    });
  }

  resetPassword(param: any) {
    return this.http.post<any>(apiUrl('/login/reset-password'), param);
  }
  updatePassword(param: any) {
    return this.http.post<any>(apiUrl('/login/update-password'), param);
  }

  updateNewPassword(param: any) {
    return this.http.post<any>(apiUrl('/login/password-update'), param);
  }

  confirmAccount(token: string): Observable<ServerResponseType> {
    const params = { token: token };
    return this.http.post<ServerResponseType>(
      apiUrl('/confirm-account'),
      params
    );
  }
}
