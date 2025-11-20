import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
type ServiceList = 'demande-agrement';
@Injectable({
  providedIn: 'root',
})
export class TransactionService {
  constructor(private http: HttpClient) {}

  create(
    service: ServiceList,
    id: string | number
  ): Observable<ServerResponseType> {
    const url = apiUrl(`/transactions/${service}/${id}`);
    return this.http.post<ServerResponseType>(url, {});
  }
}
