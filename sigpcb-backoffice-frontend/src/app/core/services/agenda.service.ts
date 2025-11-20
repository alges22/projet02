import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl, urlencode } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class AgendaService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/examens');
    return this.http.post<ServerResponseType>(url, data);
  }

  all(
    year: number | null = null,
    filters: Record<string, any> = {}
  ): Observable<ServerResponseType> {
    let url = apiUrl('/examens');
    filters['year'] = year;
    url = urlencode(url, [filters]);
    return this.http.get<ServerResponseType>(url);
  }

  status(data: any) {
    const url = apiUrl('/examens/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/examens/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/examens/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/examens/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
}
