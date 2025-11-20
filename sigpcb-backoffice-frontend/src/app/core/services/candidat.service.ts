import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class CandidatService {
  constructor(private readonly http: HttpClient) {}
  getDossierCandidats(): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats');
    return this.http.get<ServerResponseType>(url);
  }

  get(data: Record<string, any> = {}) {
    const url = apiUrl('/candidats/all');
    return this.http.get<ServerResponseType>(url, {
      params: data,
    });
  }
  history(npi: string, data: Record<string, any> = {}) {
    const url = apiUrl('/candidats/historics/' + npi);
    return this.http.get<ServerResponseType>(url, {
      params: data,
    });
  }

  authorizeExamen(data: { examen_id: number; npi: string }) {
    const url = apiUrl('/candidat-session/update');
    return this.http.post<ServerResponseType>(url, data);
  }
}
