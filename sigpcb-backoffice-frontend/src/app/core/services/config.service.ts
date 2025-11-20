import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class ConfigService {
  constructor(private http: HttpClient) {}
  get(): Observable<ServerResponseType> {
    const url = apiUrl('/configs');
    return this.http.get<ServerResponseType>(url);
  }

  postQuestionCount(data: any) {
    const url = apiUrl('/configs/question-to-compose', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }
}
