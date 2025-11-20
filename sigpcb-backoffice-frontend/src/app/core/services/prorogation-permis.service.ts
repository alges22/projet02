import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class ProrogationPermisService {
  constructor(private http: HttpClient) {}
  get(
    states: string[],
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/eservices/prorogations', 'admin');
    url += `?state=${states.join(',')}`;
    if (page !== undefined && page !== null) {
      url += `&page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  validate(
    d_prorogation_permis_id: number,
    data: { motif: string; consignes: string }
  ) {
    const url = apiUrl('/eservices/prorogations/validate');
    const d = {
      motif: data.motif,
      consigne: data.consignes,
      prorogation_id: d_prorogation_permis_id,
    };
    return this.http.post<ServerResponseType>(url, d);
  }

  reject(
    d_prorogation_permis_id: number,
    data: { motif: string; consignes: string }
  ) {
    const url = apiUrl('/eservices/prorogations/rejet');
    const d = {
      motif: data.motif,
      consigne: data.consignes,
      prorogation_id: d_prorogation_permis_id,
    };
    return this.http.post<ServerResponseType>(url, d);
  }
}
