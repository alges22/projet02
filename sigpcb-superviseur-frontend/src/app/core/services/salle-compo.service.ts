import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root'
})
export class SalleCompoService {

  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/salle-compos', 'base');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/salle-compos', 'base');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/salle-compos/' + id, 'base');
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/salle-compos/' + id, 'base');
    return this.http.delete<ServerResponseType>(url);
  }

  deleteMany(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/salle-compos/deletes', 'base');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/salle-compos/' + id, 'base');
    return this.http.put<ServerResponseType>(url, data);
  }

  status(data: any) {
    const url = apiUrl('/salle-compos/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  getSallesByAnnexeId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexeanatt-salle-compos/' + id, 'base');
    return this.http.get<ServerResponseType>(url);
  }

  postSalleCompo(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/salle-compo/multiple', 'base');
    return this.http.post<ServerResponseType>(url, data);
  }
}
