import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class UsersService {
  constructor(private readonly http: HttpClient) {}

  postAdmin(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/users');
    return this.http.post<ServerResponseType>(url, data);
  }

  getUsersAll(): Observable<ServerResponseType> {
    const url = apiUrl('/users/getall');
    return this.http.get<ServerResponseType>(url);
  }

  getAdmins(page?: number, list = 'paginate'): Observable<ServerResponseType> {
    let url = apiUrl(`/users?liste=${list}`);
    if (page) {
      url = `${url}&page=${page}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  findById(adminId: number): Observable<ServerResponseType> {
    const url = apiUrl('/users/' + adminId);
    return this.http.get<ServerResponseType>(url);
  }

  deleteAdmin(adminId: number): Observable<ServerResponseType> {
    const url = apiUrl('/users/' + adminId);
    return this.http.delete<ServerResponseType>(url);
  }

  deleteManyAdmins(adminIds: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/users/deletes');
    const data = {
      user_ids: adminIds.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, adminId: number) {
    const url = apiUrl('/users/' + adminId);
    return this.http.put<ServerResponseType>(url, data);
  }

  status(data: any) {
    const url = apiUrl('/users/status');
    return this.http.post<ServerResponseType>(url, data);
  }
  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/users/' + id);
    return this.http.delete<ServerResponseType>(url);
  }
  npiInfos(npi: any) {
    const url = apiUrl('/npi-informations');
    return this.http.post<ServerResponseType>(url, {
      npi: npi,
    });
  }
}
