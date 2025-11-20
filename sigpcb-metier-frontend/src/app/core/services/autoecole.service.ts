import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class AutoecoleService {
  url = '/auto-ecoles/';
  constructor(private http: HttpClient) {}

  getDepartements(): Observable<ServerResponseType> {
    let url = apiUrl(this.url, 'base');

    return this.http.get<ServerResponseType>(url);
  }

  findByCode(code: any): Observable<ServerResponseType> {
    const url = apiUrl('/auto-ecole/' + code, 'auto-ecole');
    return this.http.get<ServerResponseType>(url);
  }
  update(data: any, id: number) {
    const url = apiUrl(this.url + id, 'base');
    return this.http.put<ServerResponseType>(url, data);
  }
}
