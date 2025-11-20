import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class ExaminateurService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/examinateurs', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/examinateurs', 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/examinateurs/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/examinateurs/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/examinateurs/' + id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  getSalleByAnnexeId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexeanatt-salle-compos/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  getExaminateursByAnnexeId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/examinateurs-annexeanatt/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  assign(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/examinateurs/assign', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  getAssignationBySalleId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/salles-examinateurs/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  getExaminateursBySalleExamen(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/salles-examinateurs', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  getJuriesByAnnexeId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-jury/juries-annexeanatt/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  postJury(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-jury', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateJury(data: any, id: number) {
    const url = apiUrl('/annexe-jury/' + id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  deleteJury(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-jury/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }

  postImportFile(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/importation-examinateurs', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }
}
