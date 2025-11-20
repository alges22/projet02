import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class ConduiteInspectionService {
  constructor(private http: HttpClient) {}

  getSessions() {
    let url = apiUrl('/examens/all-sessions');

    return this.http.get<ServerResponseType>(url);
  }

  getJurys(session_id: number) {
    let url = apiUrl(`/conduite-inspections/examinateur-jury/${session_id}`);

    return this.http.get<ServerResponseType>(url);
  }

  getRecpats(data: any) {
    let url = apiUrl('/conduite-inspections/recapts');

    return this.http.post<ServerResponseType>(url, data);
  }

  getCandidats(data: any) {
    let url = apiUrl('/conduite-inspections/dossier-jury');

    return this.http.post<ServerResponseType>(url, data);
  }

  getAgendas(data: any) {
    let url = apiUrl('/conduite-inspections/agendas');
    return this.http.post<ServerResponseType>(url, data);
  }

  getVagues(data: any) {
    let url = apiUrl('/conduite-inspections/vagues');

    return this.http.post<ServerResponseType>(url, data);
  }

  loadCandidats(vague_id: number) {
    let url = apiUrl(`/code-inspections/vagues/${vague_id}/candidats`);

    return this.http.get<ServerResponseType>(url);
  }

  getEpreuvesByCategory(category_id: number) {
    let url = apiUrl(`/bareme-conduites/categorie-permis/${category_id}`);
    return this.http.get<ServerResponseType>(url);
  }

  getMentions() {
    let url = apiUrl('/mentions');

    return this.http.get<ServerResponseType>(url);
  }

  postAnnotation(data: any) {
    let url = apiUrl('/candidat-conduite-reponses');
    return this.http.post<ServerResponseType>(url, data);
  }

  getJuriesCandidat(jury_candidat_id: number) {
    let url = apiUrl(`/candidat-conduite-reponses/${jury_candidat_id}`);
    return this.http.get<ServerResponseType>(url);
  }

  emarges(data: FormData) {
    let url = apiUrl(`/conduites/signatures`);
    return this.http.post<ServerResponseType>(url, data);
  }

  markAsAbscent(npi: string) {
    let url = apiUrl(`/conduites/absences`);
    return this.http.post<ServerResponseType>(url, {
      npi: npi,
    });
  }

  stopCompo(data: any) {
    let url = apiUrl(`/conduite-inspections/stop-compo`);
    return this.http.post<ServerResponseType>(url, data);
  }

  getCandidatsNotes(data: any) {
    let url = apiUrl('/conduite-inspections/dossier-noter');
    return this.http.post<ServerResponseType>(url, data);
  }

  verifyCandidat(data: any) {
    let url = apiUrl('/conduite-inspections/verify-candidat');
    return this.http.get<ServerResponseType>(url, {
      params: data,
    });
  }
}
