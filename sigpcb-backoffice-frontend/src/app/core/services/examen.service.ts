import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';
import { HttpClient } from '@angular/common/http';
import { Agenda } from '../interfaces/examens';

@Injectable({
  providedIn: 'root',
})
export class ExamenService {
  private currentExamenSubject: BehaviorSubject<Agenda | null | undefined> =
    new BehaviorSubject<Agenda | null | undefined>(undefined);

  private currentSession$: Observable<Agenda | null | undefined> =
    this.currentExamenSubject.asObservable();
  constructor(private http: HttpClient) {}

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/examens/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  recentExamen(
    mois: number | undefined = undefined
  ): Observable<ServerResponseType> {
    const url = apiUrl('/examens/session-en-cours');
    return this.http.get<ServerResponseType>(url);
  }

  getExemens(data?: Record<string, any>) {
    const url = apiUrl('/examens');
    return this.http.get<ServerResponseType>(url, {
      params: data,
    });
  }

  setupCurrentSession(agenda: Agenda | null | undefined) {
    this.currentExamenSubject.next(agenda);
  }

  currentSession() {
    return this.currentSession$;
  }
}
