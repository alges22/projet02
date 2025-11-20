import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class InspecteurService {
  constructor(private readonly http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/inspecteurs', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/inspecteurs', 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/inspecteurs/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/inspecteurs/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }

  deleteMany(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/inspecteurs/deletes', 'admin');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/inspecteurs/' + id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  getSalleByAnnexeId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexeanatt-salle-compos/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  getInspecteursByAnnexeId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/inspecteurs-annexeanatt/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  assign(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/inspecteurs/assign', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  getAssignationBySalleId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/salles-inspecteurs/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  getInspecteursBySalleExamen(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/salles-inspecteurs', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }
}
