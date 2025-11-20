import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root'
})
export class ActeSigneService {

  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/acte-signables', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/acte-signables', 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/acte-signables/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/acte-signables/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }

  deleteMany(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/acte-signables/deletes', 'admin');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/acte-signables/' + id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  status(data: any) {
    const url = apiUrl('/acte-signables/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  assign(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/acte-signables/assign-signataire', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }
}
