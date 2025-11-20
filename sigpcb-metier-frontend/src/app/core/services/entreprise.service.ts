import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class EntrepriseService {
  constructor(private http: HttpClient) {}

  postSession(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements', 'entreprise');
    return this.http.post<ServerResponseType>(url, data);
  }

  resendSession(data: any, rejetId: number): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements/update-session/' + rejetId, 'entreprise');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateSession(data: any, sessionId: number) {
    const url = apiUrl('/recrutements/' + sessionId, 'entreprise');
    return this.http.put<ServerResponseType>(url, data);
  }

  soumissionSession(sessionId: number, data: any) {
    const url = apiUrl('/recrutements/close/' + sessionId, 'entreprise');
    return this.http.put<ServerResponseType>(url, data);
  }

  deleteSession(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements/' + id, 'entreprise');
    return this.http.delete<ServerResponseType>(url);
  }

  getSessions(
    page?: number,
    list = 'paginate'
  ): Observable<ServerResponseType> {
    let url = apiUrl(`/recrutements?liste=${list}`, 'entreprise');
    if (page) {
      url = `${url}&page=${page}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  findSessionById(sessionId: number): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements/' + sessionId, 'entreprise');
    return this.http.get<ServerResponseType>(url);
  }

  findInfoRejetBySessionId(sessionId: number): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements/get-rejet/' + sessionId, 'entreprise');
    return this.http.get<ServerResponseType>(url);
  }

  getLangues(): Observable<ServerResponseType> {
    const url = apiUrl('/langues-base', 'entreprise');
    return this.http.get<ServerResponseType>(url);
  }

  getCategories(): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis', 'entreprise');
    return this.http.get<ServerResponseType>(url);
  }

  getRestrictions(): Observable<ServerResponseType> {
    let url = apiUrl('/restrictions', 'recrutement-examinateur');
    return this.http.get<ServerResponseType>(url);
  }

  getDemandeParcours(): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements/entreprise-parcours', 'entreprise');
    return this.http.get<ServerResponseType>(url);
  }

  postCandidat(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements/add-candidat', 'entreprise');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateCandidat(data: any, candidatId: number) {
    const url = apiUrl(
      '/recrutements/update-candidat/' + candidatId,
      'entreprise'
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  deleteCandidat(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements/delete-candidat/' + id, 'entreprise');
    return this.http.delete<ServerResponseType>(url);
  }

  getCandidats(
    sessionId?: number,
    page?: number,
    list = 'paginate'
  ): Observable<ServerResponseType> {
    let url = apiUrl(
      '/recrutements/session-candidats/' + sessionId + '?liste=${list}',
      'entreprise'
    );
    if (page) {
      url = `${url}&page=${page}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  getCandidatById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements/show-candidat/' + id, 'entreprise');
    return this.http.get<ServerResponseType>(url);
  }

  downloadConvocationByCandidatId(id: number): Observable<ServerResponseType> {
    const url = apiUrl(
      '/recrutements/generate-convocation/' + id,
      'entreprise'
    );
    return this.http.get<ServerResponseType>(url);
  }
}
