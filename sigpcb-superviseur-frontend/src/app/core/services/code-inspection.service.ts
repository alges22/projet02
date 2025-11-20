import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class CodeInspectionService {
  constructor(private readonly http: HttpClient) {}

  getRecpats(params: any = {}) {
    let url = apiUrl('/code-inspections/recapts');
    return this.http.get<ServerResponseType>(url, {
      params: params,
    });
  }

  getSalles(params: any = {}) {
    let url = apiUrl('/code-inspections/salles');
    return this.http.get<ServerResponseType>(url, {
      params: params,
    });
  }

  verifyCandidat(data: any) {
    let url = apiUrl('/code-inspections/verify-candidat');
    return this.http.get<ServerResponseType>(url, {
      params: data,
    });
  }

  getAgendas(params: any = {}) {
    let url = apiUrl('/code-inspections/agendas');
    return this.http.get<ServerResponseType>(url, {
      params: params,
    });
  }

  getVagues(params: any = {}) {
    let url = apiUrl('/code-inspections/vagues');
    return this.http.get<ServerResponseType>(url, {
      params: params,
    });
  }

  loadCandidats(vague_id: number) {
    let url = apiUrl(`/code-inspections/vagues/${vague_id}/candidats`);
    return this.http.get<ServerResponseType>(url);
  }

  markAsAbscent(params: any = {}) {
    let url = apiUrl(`/code-inspections/mark-as-abscent`);
    return this.http.post<ServerResponseType>(url, params);
  }

  openSession(params: any = {}) {
    let url = apiUrl(`/code-inspections/open-session`);
    return this.http.post<ServerResponseType>(url, params);
  }
  emarges(data: FormData) {
    let url = apiUrl(`/code-inspections/emarges`);
    return this.http.post<ServerResponseType>(url, data);
  }

  pause(data: any) {
    let url = apiUrl(`/code-inspections/pause`);
    return this.http.post<ServerResponseType>(url, data);
  }

  startCompo(data: any) {
    let url = apiUrl(`/code-inspections/start-compo`);
    return this.http.post<ServerResponseType>(url, data);
  }

  stopCompo(data: any) {
    let url = apiUrl(`/code-inspections/stop-compo`);
    return this.http.post<ServerResponseType>(url, data);
  }
  stopCandidatCompo(params: any = {}) {
    let url = apiUrl(`/code-inspections/stop-candidat-compo`);
    return this.http.post<ServerResponseType>(url, params);
  }
  resetCompo(data: any) {
    let url = apiUrl(`/code-inspections/reset-compo`);
    return this.http.post<ServerResponseType>(url, data);
  }
}
