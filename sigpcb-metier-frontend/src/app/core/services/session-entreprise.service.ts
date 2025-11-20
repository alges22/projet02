import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class SessionEntrepriseService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/recrutements', 'entreprise');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(page?: number, list = 'paginate'): Observable<ServerResponseType> {
    let url = apiUrl(`/recrutements?liste=${list}`, 'entreprise');
    if (page) {
      url = `${url}&page=${page}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  getDemandeParcours(): Observable<ServerResponseType> {
    const url = apiUrl(
      '/examinateurs-eservices-parcours',
      'recrutement-examinateur'
    );
    return this.http.get<ServerResponseType>(url);
  }

  updateRecrutementExaminateur(data: FormData) {
    const url = apiUrl(
      '/eservices/examinateurs/update/' + data.get('rejet_id'),
      'recrutement-examinateur'
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentEchangePermis(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/echanges/payment', 'candidat');
    return this.http.post<ServerResponseType>(url, data);
  }

  postProrogationPermis(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/prorogations/store', 'candidat');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateProrogationPermis(data: FormData) {
    const url = apiUrl(
      '/eservices/prorogations/update/' + data.get('rejet_id'),
      'candidat'
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentProrogationPermis(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/prorogations/payment', 'candidat');
    return this.http.post<ServerResponseType>(url, data);
  }

  findPermisInternational(rejetId: string) {
    const url = apiUrl(
      '/eservices/permis-internationals/rejet/' + rejetId,
      'candidat'
    );
    return this.http.get<ServerResponseType>(url);
  }

  findDemande(rejetId: string) {
    const url = apiUrl(
      '/eservices/examinateurs/rejet/' + rejetId,
      'recrutement-examinateur'
    );
    return this.http.get<ServerResponseType>(url);
  }

  findEchange(rejetId: string) {
    const url = apiUrl('/eservices/echanges/rejet/' + rejetId, 'candidat');
    return this.http.get<ServerResponseType>(url);
  }
}
