import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class RecrutementMoniteurService {
  constructor(private http: HttpClient) {}

  postRecrutementMoniteur(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/moniteurs/store', 'recrutement-moniteur');
    return this.http.post<ServerResponseType>(url, data);
  }

  getDemandeParcours(): Observable<ServerResponseType> {
    const url = apiUrl('/moniteurs-eservices-parcours', 'recrutement-moniteur');
    return this.http.get<ServerResponseType>(url);
  }

  updateRecrutementMoniteur(data: FormData) {
    const url = apiUrl(
      '/eservices/moniteurs/update/' + data.get('rejet_id'),
      'recrutement-moniteur'
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  findDemande(rejetId: string) {
    const url = apiUrl(
      '/eservices/moniteurs/rejet/' + rejetId,
      'recrutement-moniteur'
    );
    return this.http.get<ServerResponseType>(url);
  }
}
