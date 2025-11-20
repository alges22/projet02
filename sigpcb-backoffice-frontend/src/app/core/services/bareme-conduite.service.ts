import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class BaremeConduiteService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/bareme-conduite');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/bareme-conduites');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/bareme-conduites/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  findBaremesByCategorieId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/bareme-conduites/categorie-permis/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/bareme-conduites/' + id);
    return this.http.delete<ServerResponseType>(url);
  }

  deleteMany(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/bareme-conduites/deletes');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/bareme-conduites/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  status(data: any) {
    const url = apiUrl('/bareme-conduites/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  getSalleById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexeanatt-salle-compos/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  postSalleCompo(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/salle-compo/multiple');
    return this.http.post<ServerResponseType>(url, data);
  }

  getSubaremes(id: number) {
    const url = apiUrl('/sub-baremes/bareme/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  postSubbareme(data: any) {
    const url = apiUrl('/sub-baremes');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateSubbareme(data: any) {
    const url = apiUrl('/sub-baremes/' + data.id);
    return this.http.put<ServerResponseType>(url, data);
  }
  deleteSubbareme(id: number) {
    const url = apiUrl('/sub-baremes/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
}
