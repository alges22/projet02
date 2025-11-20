import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { AnnexeAnatt } from '../interfaces/annexe-anatt';

@Injectable({
  providedIn: 'root',
})
export class AnnexeAnattService {
  private annexeSujet = new BehaviorSubject<AnnexeAnatt | null>(null);
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats', 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }

  deleteMany(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats/deletes', 'admin');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/annexe-annats/' + id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  status(data: any) {
    const url = apiUrl('/annexe-annats/status');
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

  selectAnnexe(annexe: AnnexeAnatt | null) {
    this.annexeSujet.next(annexe);
  }
  onAnnexeChange() {
    return this.annexeSujet.asObservable();
  }
}
