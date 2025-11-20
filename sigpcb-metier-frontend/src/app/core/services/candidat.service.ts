import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ServerResponseType } from '../types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Injectable({
  providedIn: 'root',
})
export class CandidatService {
  constructor(private http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats', 'admin');
    return this.http.post<ServerResponseType>(url, data);
  }

  getLangues(): Observable<ServerResponseType> {
    const url = apiUrl('/langues-base', 'candidat');
    return this.http.get<ServerResponseType>(url);
  }
  getDossierSession(): Observable<ServerResponseType> {
    const url = apiUrl('/candidat/dossier-session', 'candidat');
    return this.http.get<ServerResponseType>(url);
  }

  getCategoriesPermis(): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis-base', 'recrutement-examinateur');
    return this.http.get<ServerResponseType>(url);
  }

  getAutoEcoles(
    pageNumber = 1,
    liste = 'paginate',
    annexeId: number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/autoecoles?annexe_id=' + annexeId, 'candidat');
    if (liste === 'paginate') {
      url = `${url}?liste=${liste}&page=${pageNumber}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  getRestrictions(): Observable<ServerResponseType> {
    let url = apiUrl('/restrictions', 'recrutement-examinateur');
    return this.http.get<ServerResponseType>(url);
  }

  getDossierCandidatwithSessionId(id: number): Observable<ServerResponseType> {
    let url = apiUrl('/dossier-session/' + id, 'recrutement-examinateur');
    return this.http.get<ServerResponseType>(url);
  }

  checkCandidatPermisPrealable(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/check-permis-prealable', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  getCandidatDossiersWithId(id: number): Observable<ServerResponseType> {
    const url = apiUrl(
      '/dossier-candidats-souscriptions/' + id,
      'recrutement-examinateur'
    );
    return this.http.get<ServerResponseType>(url);
  }

  getCandidatDossiersParcoursWithId(): Observable<ServerResponseType> {
    const url = apiUrl(
      '/dossier-candidats-parcours',
      'recrutement-examinateur'
    );
    return this.http.get<ServerResponseType>(url);
  }

  getLastDossierCandidatWithId(): Observable<ServerResponseType> {
    const url = apiUrl(
      '/dossier-candidats-souscription',
      'recrutement-examinateur'
    );
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats/' + id, 'admin');
    return this.http.get<ServerResponseType>(url);
  }

  delete(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats/' + id, 'admin');
    return this.http.delete<ServerResponseType>(url);
  }

  deleteMany(ids: number[]): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats/deletes', 'admin');
    const data = {
      user_ids: ids.join(';'),
    };
    return this.http.post<ServerResponseType>(url, data);
  }

  update(data: any, id: number) {
    const url = apiUrl('/annexe-annats/' + id, 'admin');
    return this.http.put<ServerResponseType>(url, data);
  }

  status(data: any) {
    const url = apiUrl('/annexe-annats/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  getSalleById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexeanatt-salle-compos/' + id, 'base');
    return this.http.get<ServerResponseType>(url);
  }

  postDossierCandidatg(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateDossierCandidat(data: any, id: number) {
    const url = apiUrl('/dossier-candidats/' + id, 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  postParcoursCandidat(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-parcours', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  postJustificationAbsenceCandidat(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-justif-absences', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentCandidat(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-payments', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentCandidatJustif(data: any): Observable<ServerResponseType> {
    const url = apiUrl(
      '/dossier-candidats/justification-paiement',
      'recrutement-examinateur'
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentCandidatExpire(data: any): Observable<ServerResponseType> {
    const url = apiUrl(
      '/dossier-candidats/expire-paiement',
      'recrutement-examinateur'
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  getUserPermis(): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-permis', 'recrutement-examinateur');
    return this.http.get<ServerResponseType>(url);
  }

  savePaimentPermisNumerique(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/permis-numeriques', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  getSessions(): Observable<ServerResponseType> {
    const url = apiUrl('/examens', 'recrutement-examinateur');
    return this.http.get<ServerResponseType>(url);
  }

  updateSession(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-session', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  closeDossier(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats/close', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  openDossier(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats/open', 'recrutement-examinateur');
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaiment(data: any): Observable<ServerResponseType> {
    const url = apiUrl(
      '/eservices/permis-internationals/payment',
      'recrutement-examinateur'
    );
    return this.http.post<ServerResponseType>(url, data);
  }
}
