import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class ImageService {
  constructor(private readonly http: HttpClient) {}

  user(): Observable<ServerResponseType> {
    const url = apiUrl('/user/picture');

    return this.http.get<ServerResponseType>(url);
  }
}
