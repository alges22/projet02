import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class AgregateurService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/agregateurs');
    return this.http.post<ServerResponseType>(url, data);
  }

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/agregateurs');

    return this.http.get<ServerResponseType>(url);
  }

  status(data: any) {
    const url = apiUrl('/agregateurs/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/agregateurs/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/agregateurs/' + id);
    return this.http.post<ServerResponseType>(url, data);
  }
  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/agregateurs/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
}
