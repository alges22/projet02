import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class SettingService {
  constructor(private http: HttpClient) {}

  get(): Observable<ServerResponseType> {
    const url = apiUrl('/settings');
    return this.http.get<ServerResponseType>(url);
  }
}
