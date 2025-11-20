import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CookieService } from 'ngx-cookie';
import { Observable } from 'rxjs';
import { apiUrl, is_string, deleteCookie } from 'src/app/helpers/helpers';
import { RespType } from 'src/app/types/RespType';
import { User } from '../interfaces/user.interface';
import { StorageService } from './storage.service';

@Injectable({
  providedIn: 'root',
})
export class AuthMoniteurService {
  static EXP_DAYS = 30;
  static REDIRECTTO = '/suivre-moniteur';
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
      apiUrl(this.URLS.checklocalnpi, 'recrutement-moniteur'),
      param
    );
  }

  checknpi(param: any): Observable<any> {
    return this.http.post<any>(
      apiUrl(this.URLS.checknpi, 'recrutement-moniteur'),
      param
    );
  }

  signin(param: any): Observable<any> {
    return this.http.post<any>(
      apiUrl(this.URLS.login, 'recrutement-moniteur'),
      param
    );
  }

  signup(param: any): Observable<any> {
    return this.http.post<any>(
      apiUrl(this.URLS.register, 'recrutement-moniteur'),
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
    now.setDate(now.getDate() + AuthMoniteurService.EXP_DAYS);
    this.cookie.put('access_token', access_token, {
      expires: now,
    });
  }
  logout() {
    this.storage.remove('auth-moniteur');
    deleteCookie('access_token');
  }

  auth() {
    if (this.checked()) {
      return this.storage.get<User | null>('auth-moniteur');
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
      apiUrl('/verify-otp', 'recrutement-moniteur'),
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
      apiUrl('/resend-code', 'recrutement-moniteur'),
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
