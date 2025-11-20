import { Injectable } from '@angular/core';
import { is_array, is_string } from 'src/app/helpers/helpers';
type Storageable = 'page' | 'auth' | 'access_token' | 'question';
@Injectable({
  providedIn: 'root',
})
export class StorageService {
  constructor() {}

  store(name: Storageable, data: any, encrypt = true) {
    let dataToStore = this.serializeData(data);

    localStorage.setItem(name, dataToStore);
  }
  get<T>(name: Storageable, decrypt = true): T | null {
    let encrypted: string = localStorage.getItem(name) as string;

    if (!!encrypted) {
      return this.unSerialize(encrypted) as T;
    }
    return null;
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
