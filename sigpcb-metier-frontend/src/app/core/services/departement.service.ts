import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class DepartementService {
  constructor(private http: HttpClient) {}

  getDepartements(): Observable<ServerResponseType> {
    let url = apiUrl('/departements', 'base');

    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/departements/' + id, 'base');
    return this.http.get<ServerResponseType>(url);
  }
  update(data: any, id: number) {
    const url = apiUrl('/departements/' + id, 'base');
    return this.http.put<ServerResponseType>(url, data);
  }
}
