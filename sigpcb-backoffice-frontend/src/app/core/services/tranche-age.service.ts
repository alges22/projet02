import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/internal/Observable';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class TrancheAgeService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/tranche-ages');
    return this.http.post<ServerResponseType>(url, data);
  }

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/tranche-ages');

    return this.http.get<ServerResponseType>(url);
  }

  status(data: any) {
    const url = apiUrl('/tranche-ages/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/tranche-ages/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/tranche-ages/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }
  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/tranche-ages/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
}
