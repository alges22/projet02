import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable, of } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class DuplicataRemplacementService {
  constructor(private http: HttpClient) {}

  get(
    states: string[],
    page?: number | null,
    search?: string | null | number,
    type?: string | null
  ): Observable<ServerResponseType> {
    let url = apiUrl('/eservices/duplicatas');
    url += `?state=${states.join(',')}`;

    if (page !== undefined && page !== null) {
      url += `&page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }

    if (type !== undefined && type != null && type != 'null') {
      url += `&type=${type}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  validate(data: any) {
    const url = apiUrl('/eservices/duplicatas/validate');
    return this.http.post<ServerResponseType>(url, data);
  }

  reject(data: any) {
    const url = apiUrl('/eservices/duplicatas/rejet');
    return this.http.post<ServerResponseType>(url, data);
  }
}
