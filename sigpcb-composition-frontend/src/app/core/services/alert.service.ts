import { BehaviorSubject } from 'rxjs';
import { Injectable } from '@angular/core';
import { AlertType } from '../interfaces/alert';

@Injectable({
  providedIn: 'root',
})
export class AlertService {
  private alertSubject = new BehaviorSubject<{
    message: string;
    type: AlertType;
    call?: CallableFunction;
  } | null>(null);

  private alert$ = this.alertSubject.asObservable();

  constructor() {}

  alert(message: string, type: AlertType = 'warning', call?: CallableFunction) {
    this.alertSubject.next({
      message: message,
      type: type,
      call: call,
    });
  }

  close() {
    this.alertSubject.next(null); // Réinitialise l'alerte
  }

  onAlert() {
    return this.alert$;
  }
}
