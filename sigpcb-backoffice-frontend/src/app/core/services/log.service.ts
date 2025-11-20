import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class LogService {
  constructor(private http: HttpClient) {}
  get(data: Record<string, any> = {}) {
    const url = apiUrl('/logs');
    return this.http.get<ServerResponseType>(url, {
      params: data,
    });
  }
}
