import { Injectable } from '@angular/core';
import { AES, enc } from 'crypto-js';
import { uniqueID } from 'src/app/helpers/helpers';
@Injectable({
  providedIn: 'root',
})
export class EncryptionService {
  encrypt(text: string) {
    return AES.encrypt(text, this.secretKey()).toString();
  }

  decrypt(encrypted: string) {
    return AES.decrypt(encrypted, this.secretKey()).toString(enc.Utf8);
  }

  private secretKey() {
    let ussid = localStorage.getItem('ussid');
    if (ussid) {
      return ussid;
    } else {
      ussid = uniqueID();
      localStorage.setItem('ussid', ussid);
    }

    return ussid ?? '';
  }
}
