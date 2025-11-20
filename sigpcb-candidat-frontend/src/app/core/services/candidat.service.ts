import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';

@Injectable({
  providedIn: 'root',
})
export class CandidatService {
  constructor(private readonly http: HttpClient) {}

  post(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats');
    return this.http.post<ServerResponseType>(url, data);
  }

  getLangues(): Observable<ServerResponseType> {
    const url = apiUrl('/langues-base');
    return this.http.get<ServerResponseType>(url);
  }
  getDossierSession(): Observable<ServerResponseType> {
    const url = apiUrl('/candidat/dossier-session');
    return this.http.get<ServerResponseType>(url);
  }

  getCategoriesPermis(): Observable<ServerResponseType> {
    const url = apiUrl('/categorie-permis-base');
    return this.http.get<ServerResponseType>(url);
  }

  getAutoEcoles(
    pageNumber = 1,
    liste = 'paginate',
    annexeId: number
  ): Observable<ServerResponseType> {
    let url = apiUrl('/autoecoles?annexe_id=' + annexeId);
    if (liste === 'paginate') {
      url = `${url}?liste=${liste}&page=${pageNumber}`;
    }
    return this.http.get<ServerResponseType>(url);
  }

  getRestrictions(): Observable<ServerResponseType> {
    let url = apiUrl('/restrictions');
    return this.http.get<ServerResponseType>(url);
  }

  getDossierCandidatwithSessionId(id: number): Observable<ServerResponseType> {
    let url = apiUrl('/dossier-session/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  checkCandidatPermisPrealable(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/check-permis-prealable');
    return this.http.post<ServerResponseType>(url, data);
  }

  getCandidatDossiersWithId(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats-souscriptions/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  getCandidatDossiersParcoursWithId(): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats-parcours');
    return this.http.get<ServerResponseType>(url);
  }

  getLastDossierCandidatWithId(): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats-souscription');
    return this.http.get<ServerResponseType>(url);
  }

  findById(id: number): Observable<ServerResponseType> {
    const url = apiUrl('/annexe-annats/' + id);
    return this.http.get<ServerResponseType>(url);
  }

  status(data: any) {
    const url = apiUrl('/annexe-annats/status');
    return this.http.post<ServerResponseType>(url, data);
  }

  postDossierCandidatg(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats');
    return this.http.post<ServerResponseType>(url, data);
  }

  postInscriptionReconduit(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats/externals');
    return this.http.post<ServerResponseType>(url, data);
  }
  editDossierCandidat(data: FormData): Observable<ServerResponseType> {
    const url = apiUrl(
      '/update-dossier-candidats/' + data.get('dossier_session_id')
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  updateDossierCandidat(data: any, id: number) {
    const url = apiUrl('/dossier-candidats/' + id);
    return this.http.post<ServerResponseType>(url, data);
  }

  postParcoursCandidat(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-reconduits');
    return this.http.post<ServerResponseType>(url, data);
  }

  postJustificationAbsenceCandidat(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-justif-absences');
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentCandidat(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-payments');
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentCandidatJustif(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats/justification-paiement');
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentCandidatExpire(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats/expire-paiement');
    return this.http.post<ServerResponseType>(url, data);
  }

  saveSessionCandidatExpire(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats/session-expires');
    return this.http.post<ServerResponseType>(url, data);
  }

  getUserPermis(): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-permis');
    return this.http.get<ServerResponseType>(url);
  }

  savePaimentPermisNumerique(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/permis-numeriques');
    return this.http.post<ServerResponseType>(url, data);
  }

  demandeAttestation(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/attestation/store');
    return this.http.post<ServerResponseType>(url, data);
  }

  getSessions(): Observable<ServerResponseType> {
    const url = apiUrl('/examens');
    return this.http.get<ServerResponseType>(url);
  }

  updateSession(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/candidat-sessions');
    return this.http.post<ServerResponseType>(url, data);
  }

  closeDossier(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats/close');
    return this.http.post<ServerResponseType>(url, data);
  }

  openDossier(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/dossier-candidats/open');
    return this.http.post<ServerResponseType>(url, data);
  }

  postPermisInternational(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/permis-internationals/store');
    return this.http.post<ServerResponseType>(url, data);
  }

  updatePermisInternational(data: FormData) {
    const url = apiUrl(
      '/eservices/permis-internationals/update/' + data.get('rejet_id')
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaiment(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/permis-internationals/payment');
    return this.http.post<ServerResponseType>(url, data);
  }

  postEchangePermis(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/echanges/store');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateEchangePermis(data: FormData) {
    const url = apiUrl('/eservices/echanges/update/' + data.get('rejet_id'));
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentEchangePermis(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/echanges/payment');
    return this.http.post<ServerResponseType>(url, data);
  }

  postProrogationPermis(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/prorogations/store');
    return this.http.post<ServerResponseType>(url, data);
  }

  updateProrogationPermis(data: FormData) {
    const url = apiUrl(
      '/eservices/prorogations/update/' + data.get('rejet_id')
    );
    return this.http.post<ServerResponseType>(url, data);
  }

  savePaimentProrogationPermis(data: any): Observable<ServerResponseType> {
    const url = apiUrl('/eservices/prorogations/payment');
    return this.http.post<ServerResponseType>(url, data);
  }

  findPermisInternational(rejetId: string) {
    const url = apiUrl('/eservices/permis-internationals/rejet/' + rejetId);
    return this.http.get<ServerResponseType>(url);
  }

  findProrogation(rejetId: string) {
    const url = apiUrl('/eservices/prorogations/rejet/' + rejetId);
    return this.http.get<ServerResponseType>(url);
  }

  findEchange(rejetId: string) {
    const url = apiUrl('/eservices/echanges/rejet/' + rejetId);
    return this.http.get<ServerResponseType>(url);
  }

  justifierAbsence(data: any) {
    const url = apiUrl('/justification-absences');
    return this.http.post<ServerResponseType>(url, data);
  }
}
