import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class AnnexeAnattService {
  constructor(private http: HttpClient) {}

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  getSalleById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexeanatt-salle-compos/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  postSalleCompo(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/salle-compo/multiple');
    return this.http.post<ServerResponseType>(url, data);
  }
}
