import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class ImageService {
  constructor(private readonly http: HttpClient) {}

  getImages(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidats/images');
    return this.http.post<ServerResponseType>(url, data);
  }
}
