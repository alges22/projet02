import { Injectable } from '@angular/core';
import { is_array, is_string } from 'src/app/helpers/helpers';
import { EncryptionService } from './encryption.service';
type Storegeable =
  | 'auth'
  | 'page'
  | 'consent.cookie'
  | 'demande_agrement'
  | 'demande_moniteurs'
  | 'new-registration'
  | 'promoteur'
  | 'demande-page'
  | 'trt'
  | 'moniteur'
  | 'ae';
@Injectable({
  providedIn: 'root',
})
export class StorageService {
  constructor(private encryption: EncryptionService) {}

  store<T>(name: Storegeable, data: T, encrypt = true) {
    let dataToStore = this.serializeData(data);
    if (encrypt) {
      dataToStore = this.encryption.encrypt(dataToStore);
    }
    localStorage.setItem(name, dataToStore);
  }
  get<T>(name: Storegeable, decrypt = true): T | null {
    let encrypted: string = localStorage.getItem(name) as string;

    let decrypted: string | null = null;
    if (decrypt && encrypted) {
      try {
        decrypted = this.encryption.decrypt(encrypted);
      } catch (error) {
        return null;
      }
    }
    return this.unSerialize(decrypted) as T;
  }
  has(name: Storegeable) {
    return is_string(localStorage.getItem(name));
  }

  remove(name: Storegeable) {
    localStorage.removeItem(name);
  }

  private serializeData(data: any) {
    if (typeof data == 'object') {
      return JSON.stringify(data);
    } else if (is_array(data)) {
      return JSON.stringify(data);
    } else if (typeof data === 'boolean') {
      return data ? 'true' : 'false';
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
