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
    const url = apiUrl('/annexe-anatts');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-anatts/' + id);
    return this.http.get<ServerResponseType>(url);
  }
}
