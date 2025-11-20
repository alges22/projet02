import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ServerResponseType } from '../types/server-response.type';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class QuestionService {
  constructor(private readonly http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/questions', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  regenere(): Observable<ServerResponseType> {
    const url = apiUrl('/questions/distribute-questions', 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  get(page?: number, liste = 'paginate'): Observable<ServerResponseType> {
    let url = apiUrl('/questions', 'admin');
    if (liste == 'paginate') {
      url = `${url}?liste=${liste}&page=${page}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: any): Observable<ServerResponseType> {
    const url = apiUrl('/questions/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/questions/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }

  deleteMany(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/questions/deletes', 'admin');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/questions/' + id, 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  status(data: any) {
    const url = apiUrl('/questions/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  getSalleById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexeanatt-salle-compos/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  postAudio(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/questions/audio', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  getAudio(): Observable<ServerResponseType> {
    const url = apiUrl('/questions/audios', 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  deleteAudio(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/questions/audio/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }

  postReponseByQuestion(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/questions/reponse', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateReponseByQuestion(data: any, id: number) {
    const url = apiUrl('/questions/update-reponse/' + id, 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  deleteReponseByQuestion(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/questions/reponse/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }
}
