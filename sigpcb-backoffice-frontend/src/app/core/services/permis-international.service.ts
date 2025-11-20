import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class PermisInternationalService {
  constructor(private http: HttpClient) {}
  get(
    states: string[],
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/eservices/permis-internationals', 'admin');
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
    d_permis_inter_id: number,
    data: { motif: string; consignes: string }
  ) {
    const url = apiUrl('/eservices/permis-internationals/validate');
    const d = {
      motif: data.motif,
      consigne: data.consignes,
      permis_international_id: d_permis_inter_id,
    };
    return this.http.post<ServerResponseType>(url, d);
  }

  reject(
    d_permis_inter_id: number,
    data: { motif: string; consignes: string }
  ) {
    const url = apiUrl('/eservices/permis-internationals/rejet');
    const d = {
      motif: data.motif,
      consigne: data.consignes,
      permis_international_id: d_permis_inter_id,
    };
    return this.http.post<ServerResponseType>(url, d);
  }
}
