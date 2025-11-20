import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CookieService } from 'ngx-cookie';
import { Observable } from 'rxjs';
import { apiUrl, deleteCookie, is_string } from 'src/app/helpers/helpers';
import { StorageService } from './storage.service';
import { RespType } from 'src/app/types/RespType';
import { ServerResponseType } from '../types/server-response.type';
import {
  Moniteur,
  Promoteur,
  TempAutoEcole,
} from '../interfaces/user.interface';

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

  signinMoniteur(param: any): Observable<any> {
    return this.http.post<any>(apiUrl('/moniteurs/login'), param);
  }

  verifyMoniteur(param: any): Observable<any> {
    return this.http.post<any>(apiUrl('/moniteurs/verify'), param);
  }

  resendMoniteurOTP(param: any): Observable<any> {
    return this.http.post<any>(apiUrl('/moniteurs/resend-code'), param);
  }

  signup(param: any): Observable<ServerResponseType> {
    return this.http.post<ServerResponseType>(apiUrl('/register'), param);
  }
  sendOtp(param: any): Observable<ServerResponseType> {
    return this.http.post<ServerResponseType>(
      apiUrl('/generate-phone-otp'),
      param
    );
  }

  verifyPhone(param: any): Observable<ServerResponseType> {
    return this.http.post<ServerResponseType>(apiUrl('/verify-phone'), param);
  }
  checked() {
    const access_token = this.cookie.get('access_token');
    if (!access_token) {
      return false;
    }
    return is_string(access_token) && access_token.length > 1;
  }
  checkifu(ifu: any, param: any): Observable<any> {
    return this.http.post<any>(apiUrl(`/verify-ifu/${ifu}`), param);
  }

  verifyIfu(param: any): Observable<any> {
    return this.http.post<any>(apiUrl(`/verify-ifu`), param);
  }

  resendIfuCode(param: any): Observable<any> {
    return this.http.post<any>(apiUrl(`/resend-ifu-code`), param);
  }

  npi(param: any): Observable<any> {
    return this.http.post<any>(apiUrl('/npi-candidat'), param);
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
    this.storage.remove('ae');
    this.storage.remove('promoteur');
    this.storage.remove('moniteur');
    deleteCookie('access_token');
  }

  moniteur(): Moniteur | null {
    const auth = this.auth();

    if (!auth) {
      return null;
    }
    return auth;
  }

  authIsMoniteur() {
    const m: any = this.moniteur();

    if (m !== null) {
      return m['type'] == 'moniteur';
    }

    return false;
  }

  auth() {
    if (this.checked()) {
      const auth = this.storage.get<Moniteur | Promoteur | null>('auth');

      if (typeof auth === 'object' && auth !== undefined && auth !== null) {
        return auth;
      }
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

  monitoringAes(param: Record<string, string>) {
    return this.http.get<RespType>(apiUrl('/monitoring-aes'), {
      params: param,
    });
  }
}
