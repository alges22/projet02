import { UrlMaker } from './../../helpers/url-maker';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl, parseUrl } from 'src/app/helpers/helpers';
import { HttpClient, HttpParams } from '@angular/common/http';
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
    const url = apiUrl('/programmations/generate');
    return this.http.post<ServerResponseType>(url, data);
  }

  distributeIntoSalle(data: {
    annexe_id: number;
    examen_id: number;
  }): Observable<ServerResponseType> {
    const url = apiUrl('/programmations/distribute-into-salle');
    return this.http.post<ServerResponseType>(url, data);
  }

  getDossiers(param: KeyValueParam[]) {
    let url = apiUrl('/dossier-sessions');
    url = parseUrl(url, param);

    return this.http.get<ServerResponseType>(url);
  }

  statistiques(param: KeyValueParam[]) {
    let url = apiUrl('/programmations/statistiques');
    url = parseUrl(url, param);

    return this.http.get<ServerResponseType>(url);
  }

  programmations(param: KeyValueParam[]) {
    let url = apiUrl('/programmations');
    url = parseUrl(url, param);

    return this.http.get<ServerResponseType>(url);
  }

  setAnnexeCompo(id: number | null) {
    this.currentAnnexeSubject.next(id);
  }

  currentAnnexeId() {
    return this.selectedAnnexeId$;
  }

  getCandidatsConduite(param: KeyValueParam[]) {
    let url = apiUrl('/conduite/resultat-code');
    url = parseUrl(url, param);

    return this.http.get<ServerResponseType>(url);
  }

  programmationsConduite(param: KeyValueParam[]) {
    let url = apiUrl('/conduite/programmations');
    url = parseUrl(url, param);

    return this.http.get<ServerResponseType>(url);
  }

  generateConduite(data: {
    annexe_id: number;
    examen_id: number;
  }): Observable<ServerResponseType> {
    const url = apiUrl('/conduite/generate');
    return this.http.post<ServerResponseType>(url, data);
  }

  juriesDistrution(data: {
    annexe_id: number;
    examen_id: number;
  }): Observable<ServerResponseType> {
    const url = apiUrl('/conduite/jury-distribution');
    return this.http.post<ServerResponseType>(url, data);
  }

  sendConvocationd(data: any) {
    const url = apiUrl('/convocations');
    return this.http.post<ServerResponseType>(url, data);
  }

  sendConvocationConduite(data: any) {
    const url = apiUrl('/conduite/convocations');
    return this.http.post<ServerResponseType>(url, data);
  }
}
