import { ProfileData } from 'src/app/core/interfaces/profiles';
import { Injectable } from '@angular/core';
import { Observable, BehaviorSubject, catchError } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { apiUrl } from 'src/app/helpers/helpers';
import { ServerResponseType } from '../types/server-response.type';
import { AlertService } from './alert.service';

/**
 * Service pour gérer les données du profil utilisateur.
 */
@Injectable({
  providedIn: 'root',
})
export class ProfileService {
  constructor(private http: HttpClient, private alertService: AlertService) {}

  profileData: ProfileData | null = null;
  private connectionSubject = new BehaviorSubject<boolean>(false);
  // Sujet BehaviorSubject pour émettre les données du profil.
  private profileDataSubject = new BehaviorSubject<ProfileData | null>(null);

  // Observable pour observer les mises à jour des données du profil.
  private profileData$ = this.profileDataSubject.asObservable();

  /**
   * Obtient les données du profil. Si les données ne sont pas déjà en mémoire,
   * elles sont récupérées depuis le serveur avant d'être renvoyées.
   * @returns Un observable des données du profil.
   */
  get(): Observable<ProfileData | null> {
    this._sync();
    return this.profileData$;
  }

  set(profileData: ProfileData) {
    // Émet les données du profil aux abonnés.
    this.profileDataSubject.next(profileData);
  }

  private _sync() {
    this.profileData = this.profileDataSubject.value;
    if (!this.profileData) {
      this.http
        .get<any>(apiUrl('/profiles'))
        .pipe(
          catchError((e: any) => {
            const error = e.error as ServerResponseType;
            let message = error.message || 'Un problème inatttendu est survenu';
            this.alertService.alert(message, 'danger');

            return [];
          })
        )
        .subscribe((response) => {
          const data: any = response.data;
          this.set(data);
        });
    }
  }
}
