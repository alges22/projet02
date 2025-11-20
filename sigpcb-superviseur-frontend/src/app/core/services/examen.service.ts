import { BehaviorSubject, Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';
import { HttpClient } from '@angular/common/http';
import { Agenda } from '../interfaces/date';

@Injectable({
  providedIn: 'root',
})
export class ExamenService {
  private currentExamenSubject: BehaviorSubject<Agenda | null | undefined> =
    new BehaviorSubject<Agenda | null | undefined>(undefined);

  private currentSession$: Observable<Agenda | null | undefined> =
    this.currentExamenSubject.asObservable();

  constructor(private http: HttpClient) {}

  getExemens(filters: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/examens');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  sessionEnCours(filters: Record<string, number | string | null>[] = []) {
    let url = apiUrl('/examens/session-en-cours');

    if (filters.length) {
      url = urlencode(url, filters);
    }
    return this.http.get<ServerResponseType>(url);
  }

  setupCurrentSession(agenda: Agenda | null | undefined) {
    this.currentExamenSubject.next(agenda);
  }

  currentSession() {
    return this.currentSession$;
  }
}
