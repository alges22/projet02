import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class PhotoService {
  constructor(private readonly http: HttpClient) {}

  get(npis: string[]): Observable<ServerResponseType> {
    return this.http.post(apiUrl('/photo-by-candidats'), {
      npis: npis,
    });
  }
}
