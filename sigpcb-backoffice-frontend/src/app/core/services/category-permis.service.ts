import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class CategoryPermisService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis');
    return this.http.post<ServerResponseType>(url, data);
  }

  all(): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis');

    return this.http.get<ServerResponseType>(url);
  }

  status(data: any) {
    const url = apiUrl('/categorie-permis/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  update(data: any, id: number) {
    const url = apiUrl('/categorie-permis/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }
  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
  assignTrancheAge(data: any) {
    const url = apiUrl('/cat-permis-tranches');
    return this.http.post<ServerResponseType>(url, data);
  }
  /**
   * Retire une extenson du permis
   * @param id
   * @returns
   */
  removeCatExtension(id: number) {
    const url = apiUrl(`/categorie-permis/extension/${id}`);
    return this.http.delete<ServerResponseType>(url);
  }

  addExtension(data: {
    categorie_permis_id: number;
    categorie_permis_extensible_id: number;
  }) {
    const url = apiUrl('/categorie-permis/extension');
    return this.http.post<ServerResponseType>(url, data);
  }

  getExtensions(): Observable<ServerResponseType> {
    const url = apiUrl(`/categorie-permis/extensions`);
    return this.http.get<ServerResponseType>(url);
  }
  getTrancheAges() {
    const url = apiUrl('/cat-permis-tranches');

    return this.http.get<ServerResponseType>(url);
  }
}
