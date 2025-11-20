import { Observable, map, of } from 'rxjs';
import { ServerResponseType } from './../types/server-response.type';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class TitreService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/titres');
    return this.http.post<ServerResponseType>(url, data);
  }

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/titres');

    return this.http.get<ServerResponseType>(url);
  }
  status(data: any) {
    const url = apiUrl('/titres/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/titres/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/titres/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/titres/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
}
