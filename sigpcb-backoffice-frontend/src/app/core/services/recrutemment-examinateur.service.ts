import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { RecrutementEntreprise } from '../interfaces/recreutement';

@Injectable({
  providedIn: 'root',
})
export class RecrutemmentExaminateurService {
  constructor(private http: HttpClient) {}
  get(
    states: string[],
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/eservices/examinateurs', 'admin');
    url += `?state=${states.join(',')}`;
    if (page !== undefined && page !== null) {
      url += `&page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  validate(data: any) {
    const url = apiUrl('/eservices/examinateurs/validate');
    return this.http.post<ServerResponseType>(url, data);
  }

  validateDemandeExaminateur(demandeexaminateur: any) {
    const url = apiUrl('/demande-examinateurs/validate');
    return this.http.post<ServerResponseType>(url, demandeexaminateur);
  }

  rejectDemandeExaminateur(
    d_examinateur_id: number,
    data: { motif: string; consigne: string }
  ) {
    const url = apiUrl('/eservices/examinateurs/rejet');
    const d = {
      motif: data.motif,
      consigne: data.consigne,
      demande_examinateur_id: d_examinateur_id,
    };
    return this.http.post<ServerResponseType>(url, d);
  }

  reject(
    d_examinateur_permis_id: number,
    data: { motif: string; consignes: string }
  ) {
    const url = apiUrl('/eservices/examinateurs/rejet');
    const d = {
      motif: data.motif,
      consigne: data.consignes,
      demande_examinateur_id: d_examinateur_permis_id,
    };
    return this.http.post<ServerResponseType>(url, d);
  }

  getEntreprises(
    states: string[],
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/entreprises', 'admin');
    url += `?state=${states.join(',')}`;
    if (page !== undefined && page !== null) {
      url += `&page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }
    return this.http.get<ServerResponseType>(url);
  }
  addEntreprise(form: FormData | RecrutementEntreprise, id?: number) {
    if (!id) {
      const url = apiUrl('/entreprises');
      return this.http.post<ServerResponseType>(url, form);
    } else {
      const url = apiUrl('/entreprises/' + id);
      return this.http.put<ServerResponseType>(url, form);
    }
  }

  getEntrepriseSessions(
    states: string[],
    id: number | null,
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/entreprises/recrutements/' + id, 'admin');
    if (!id) {
      url = apiUrl('/entreprises/get-recrutements');
    }

    if (states.length) {
      url += `?state=${states.join(',')}`;
    }
    if (page !== undefined && page !== null) {
      if (states.length) {
        url += `&page=${page}`;
      } else {
        url = `?page=${page}`;
      }
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  getEntrepriseSessionCandidats(
    id: number,
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/entreprises/recrutement/' + id + '/candidats', 'admin');
    if (page !== undefined && page !== null) {
      url += `?page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  validateOrRejectDemandeRecrutement(
    demande: any,
    action: 'validate' | 'rejet'
  ) {
    let url = apiUrl('/entreprises/' + action);
    return this.http.post<ServerResponseType>(url, demande);
  }

  deleteEntreprise(data: number) {
    const url = apiUrl('/entreprises/' + data);
    return this.http.delete<ServerResponseType>(url);
  }

  getSessionsByAnnexeId(
    states: string[],
    id: number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/entreprises/annexe-recrutements/' + id, 'admin');
    if (states.length) {
      url += `?state=${states.join(',')}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  getCandidatsBySessionIdEntreprise(
    id: number,
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/entreprises/session/' + id + '/candidats', 'admin');
    if (page !== undefined && page !== null) {
      url += `?page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  getResultatsBySessionIdEntreprise(
    id: number,
    page?: number | null,
    search?: string | null | number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/entreprises/resultats/' + id, 'admin');
    if (page !== undefined && page !== null) {
      url += `?page=${page}`;
    }
    if (search !== undefined && search !== null) {
      url += `&search=${search}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  sendConvocation(data: any, id: number) {
    const url = apiUrl('/entreprises/send-convocations/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  stopCompo(data: any, id: number) {
    const url = apiUrl('/entreprises/end-compo/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  startCompo(data: any) {
    const url = apiUrl('/entreprises/start-compo');
    return this.http.post<ServerResponseType>(url, data);
  }

  sendResultat(data: any, id: number) {
    const url = apiUrl('/entreprises/send-resultats/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  getEpreuve(sessionId: number): Observable<ServerResponseType> {
    const url = apiUrl('/entreprises/get-conduite-epreuves/' + sessionId);

    return this.http.get<ServerResponseType>(url);
  }

  findEpreuveById(epreuveId: number): Observable<ServerResponseType> {
    const url = apiUrl('/entreprises/show-epreuve/' + epreuveId);
    return this.http.get<ServerResponseType>(url);
  }

  postEpreuve(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/entreprises/add-comduite-epreuves');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateEpreuve(data: any, id: number) {
    const url = apiUrl('/entreprises/update-conduite-epreuve/' + id);
    return this.http.put<ServerResponseType>(url, data);
  }

  deleteEpreuve(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/entreprises/delete-conduite-epreuve/' + id);
    return this.http.delete<ServerResponseType>(url);
  }

  annotation(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/entreprises/candidat-conduite-notes');
    return this.http.post<ServerResponseType>(url, data);
  }
}
