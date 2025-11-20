import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root'
})
export class RoleService {

  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/roles');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/roles');

    return this.http.get<ServerResponseType>(url);
  }
  status(data: any) {
    const url = apiUrl('/roles/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/roles/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/roles/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/roles/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
}
