import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CookieService } from 'ngx-cookie';
import { Observable } from 'rxjs';
import { apiUrl, deleteCookie, is_string } from 'src/app/helpers/helpers';
import { StorageService } from './storage.service';
import { User } from '../interfaces/user.interface';
import { RespType } from 'src/app/types/RespType';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  static EXP_DAYS = 30;
  static REDIRECTTO = '/suivre-demande';
  static REDIRECTENTREPRISETO = '/entreprise/dashboard';
  static REDIRECTMONITEURTO = '/moniteur/dashboard';
  URLS = {
    checknpi: '/npi-candidat',
    checklocalnpi: '/verify-npi',
    login: '/login',
    register: '/register',
  };
  constructor(
    private http: HttpClient,
    private cookie: CookieService,
    private storage: StorageService
  ) {}

  checklocalnpi(param: any): Observable<any> {
    return this.http.post<any>(
      apiUrl(this.URLS.checklocalnpi, 'recrutement-examinateur'),
      param
    );
  }

  checknpi(param: any): Observable<any> {
    return this.http.post<any>(
      apiUrl(this.URLS.checknpi, 'recrutement-examinateur'),
      param
    );
  }

  signin(param: any): Observable<any> {
    return this.http.post<any>(
      apiUrl(this.URLS.login, 'recrutement-examinateur'),
      param
    );
  }

  signup(param: any): Observable<any> {
    return this.http.post<any>(
      apiUrl(this.URLS.register, 'recrutement-examinateur'),
      param
    );
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
    this.storage.remove('auth-entreprise');
    deleteCookie('access_token');
  }

  auth() {
    if (this.checked()) {
      return this.storage.get<User | null>('auth');
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
    ///api/anatt-admin/login/resend-code
    return this.http.post<any>(
      apiUrl('/verify-otp', 'recrutement-examinateur'),
      param
    );
  }

  /**
   * Renvoie Opt
   * @param param
   * @returns
   */
  resendOpt(param: any) {
    return this.http.post<any>(
      apiUrl('/resend-code', 'recrutement-examinateur'),
      param
    );
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

  checkedUserHasPermis() {
    if (!this.storage.get<any | null>('permis')?.has_permis) {
      return false;
    }
    return true;
  }

  signinEntreprise(param: any): Observable<any> {
    return this.http.post<any>(apiUrl(this.URLS.login, 'entreprise'), param);
  }

  optEntreprise(param: any) {
    ///api/anatt-admin/login/resend-code
    return this.http.post<any>(apiUrl('/verify-otp', 'entreprise'), param);
  }

  resendOptEntreprise(param: any) {
    return this.http.post<any>(apiUrl('/resend-code', 'entreprise'), param);
  }
}
