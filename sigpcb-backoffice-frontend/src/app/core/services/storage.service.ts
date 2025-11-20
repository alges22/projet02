import { Injectable } from '@angular/core';
import { is_array, is_string } from 'src/app/helpers/helpers';
import { EncryptionService } from './encryption.service';
type Storageable =
  | 'at-pmss'
  | 'at-rls'
  | 'auth'
  | 'current-annexe'
  | 'current-session'
  | 'last_used';
@Injectable({
  providedIn: 'root',
})
export class StorageService {
  constructor(private encryption: EncryptionService) {}

  store(name: Storageable, data: any, encrypt = true) {
    let dataToStore = this.serializeData(data);
    if (encrypt) {
      dataToStore = this.encryption.encrypt(dataToStore);
    }
    localStorage.setItem(name, dataToStore);
  }
  get<T>(name: Storageable, decrypt = true): T | null {
    let encrypted: string = localStorage.getItem(name) as string;
    let decrypted: string | null = null;
    if (decrypt && encrypted) {
      decrypted = this.encryption.decrypt(encrypted);
    }
    return this.unSerialize(decrypted) as T;
  }
  has(name: Storageable) {
    return is_string(localStorage.getItem(name));
  }

  remove(name: Storageable) {
    localStorage.removeItem(name);
  }

  private serializeData(data: any) {
    if (typeof data == 'object') {
      return JSON.stringify(data);
    } else if (is_array(data)) {
      return JSON.stringify(data);
    }
    return data;
  }

  private unSerialize(data: any) {
    if (!data) {
      return null;
    }
    try {
      return JSON.parse(data);
    } catch (error) {
      return data;
    }
  }

  destroy() {
    localStorage.clear();
  }
}
