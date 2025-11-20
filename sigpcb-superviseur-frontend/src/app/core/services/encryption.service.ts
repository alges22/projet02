import { Injectable } from '@angular/core';
import { AES, enc } from 'crypto-js';
import { CookieService } from 'ngx-cookie';
import { environment } from 'src/environments/environment';
@Injectable({
  providedIn: 'root',
})
export class EncryptionService {
  constructor(private cookieService: CookieService) {}
  encrypt(text: string) {
    return AES.encrypt(text, this.secretKey()).toString();
  }

  decrypt(encrypted: string) {
    return AES.decrypt(encrypted, this.secretKey()).toString(enc.Utf8);
  }

  private secretKey() {
    const access_token = this.cookieService.get('access_token');
    let secret = access_token;
    if (access_token) {
      secret = environment.api_key + access_token.substr(0, 16);
    }
    return secret ?? '';
  }
}
