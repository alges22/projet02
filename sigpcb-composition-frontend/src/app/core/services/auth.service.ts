import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl, is_string } from 'src/app/helpers/helpers';
import { StorageService } from './storage.service';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  static REDIRECTTO = '/compos';
  constructor(private http: HttpClient, private storage: StorageService) {}

  signin(param: { code: string }): Observable<any> {
    return this.http.post<any>(apiUrl('/login'), param);
  }

  checked() {
    const access_token = this.storage.get<string | null>('access_token');
    if (!access_token) {
      return false;
    }
    return is_string(access_token) && access_token.length > 1;
  }

  attempt(access_token: string) {
    this.storage.store('access_token', access_token);
  }
  logout() {
    this.storage.destroy();
  }
}
