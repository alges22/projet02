import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { Affiliation } from '../interfaces';

@Injectable({
  providedIn: 'root',
})
export class AffiliationService {
  constructor(private http: HttpClient) {}

  post(data: Affiliation): Observable<ServerResponseType> {
    const url = apiUrl('/inscription-candidats');
    return this.http.post<ServerResponseType>(url, data);
  }

  get(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/inscription-candidats');
    return this.http.get<ServerResponseType>(url, {
      params: data,
    });
  }
}
