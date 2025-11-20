import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl, parseUrl } from 'src/app/helpers/helpers';
import { HttpClient } from '@angular/common/http';
import { KeyValueParam } from 'src/app/helpers/types';

@Injectable({
  providedIn: 'root',
})
export class CompositionService {
  private currentAnnexeSubject: BehaviorSubject<any> = new BehaviorSubject<any>(
    null
  );

  private selectedAnnexeId$: Observable<any> =
    this.currentAnnexeSubject.asObservable();
  constructor(private http: HttpClient) {}

  generateComposition(data: {
    annexe_id: number;
    examen_id: number;
  }): Observable<ServerResponseType> {
    const url = apiUrl('/compositions/generate');
    return this.http.post<ServerResponseType>(url, data);
  }

  distributeIntoSalle(data: {
    annexe_id: number;
    examen_id: number;
  }): Observable<ServerResponseType> {
    const url = apiUrl('/compositions/distribute-into-salle');
    return this.http.post<ServerResponseType>(url, data);
  }

  getDossiers(param: KeyValueParam[]) {
    let url = apiUrl('/dossier-sessions');
    url = parseUrl(url, param);

    return this.http.get<ServerResponseType>(url);
  }

  getCandidats(param: KeyValueParam[]) {
    let url = apiUrl('/compositions/dossier-sessions');
    url = parseUrl(url, param);

    return this.http.get<ServerResponseType>(url);
  }

  programmations(param: KeyValueParam[]) {
    let url = apiUrl('/compositions/programmations');
    url = parseUrl(url, param);

    return this.http.get<ServerResponseType>(url);
  }

  setAnnexeCompo(id: number | null) {
    this.currentAnnexeSubject.next(id);
  }

  currentAnnexeId() {
    return this.selectedAnnexeId$;
  }

  startCompo() {}
}
