import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { HttpClient } from '@angular/common/http';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class AuthenticiteDuPermisService {
  constructor(private http: HttpClient) {}

  get(
    states: string[],
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/eservices/authenticites');
    url += `?state=${states.join(',')}`;

    if (page !== undefined && page !== null) {
      url += `&page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }

    return this.http.get<ServerResponseType>(url);
  }

  validate(data: any) {
    const url = apiUrl('/eservices/authenticites/validate');
    return this.http.post<ServerResponseType>(url, data);
  }

  reject(data: any) {
    const url = apiUrl('/eservices/authenticites/rejet');
    return this.http.post<ServerResponseType>(url, data);
  }
}
