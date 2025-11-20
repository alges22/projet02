import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class HistoriqueService {
  constructor(private http: HttpClient) {}

  get() {
    const url = apiUrl('/historiques');
    return this.http.get<ServerResponseType>(url);
  }
}
