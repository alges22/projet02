import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';
import { Observable } from 'rxjs';

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
