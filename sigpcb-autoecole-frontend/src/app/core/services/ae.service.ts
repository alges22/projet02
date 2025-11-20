import { Injectable } from '@angular/core';
import { Ae, AutoEcole } from '../interfaces/user.interface';
import { BehaviorSubject, Observable } from 'rxjs';
import { StorageService } from './storage.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Location } from '@angular/common';
import { refresh } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class AeService {
  private aeSubject: BehaviorSubject<AutoEcole[]> = new BehaviorSubject<
    AutoEcole[]
  >([]);
  currentAe: Ae | null = null;

  private aes$: Observable<AutoEcole[]> = this.aeSubject.asObservable();
  constructor(private storage: StorageService) {}

  setAes(aes: AutoEcole[]) {
    this.aeSubject.next(aes);
  }

  getAes() {
    return this.aes$;
  }
  select(select: Ae | null, fresh = false) {
    this.currentAe = select;
    this.storage.store('ae', select);
    if (fresh) {
      refresh();
    }
  }

  getAe(): Ae | null {
    if (this.currentAe) {
      return this.currentAe;
    }

    const ae = this.storage.get('ae') as Ae;
    if (!!ae && typeof ae == 'object') {
      return ae;
    }

    return null;
  }
}
