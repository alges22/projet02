import { Component } from '@angular/core';
import { CompoRecrutementService } from 'src/app/core/services/compo-recrutement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutemmentExaminateurService } from 'src/app/core/services/recrutemment-examinateur.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-resultat-final',
  templateUrl: './resultat-final.component.html',
  styleUrls: ['./resultat-final.component.scss'],
})
export class ResultatFinalComponent {
  annexeId: any;
  sessions: any[] = [];
  candidats: any[] = [];
  sessionId: any;
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private composition: CompoRecrutementService,
    private recrutementExaminateurService: RecrutemmentExaminateurService
  ) {}

  ngOnInit(): void {
    this.composition.currentAnnexeId().subscribe((annexeId) => {
      if (annexeId !== null) {
        this.annexeId = annexeId;
        this.getSessionsByAnnexeId(this.annexeId);
      }
    });
  }

  private getSessionsByAnnexeId(id: any) {
    this.errorHandler.startLoader('Récupération des sessions ...');
    this.candidats = [];
    const states = ['validate'];
    this.recrutementExaminateurService
      .getSessionsByAnnexeId(states, id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.sessions = response.data;
        this.errorHandler.stopLoader();
      });
  }

  sessionSelected(event: any): void {
    if (event.target.value != 0) {
      this.sessionId = event.target.value;
      this.errorHandler.startLoader('Récupération des candidats ...');
      this.recrutementExaminateurService
        .getResultatsBySessionIdEntreprise(event.target.value)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.candidats = response.data;
          this.errorHandler.stopLoader();
        });
    }
  }

  sendResultat(): void {
    const data = {};
    this.errorHandler.startLoader('Envoi du résultat ...');
    // this.composition.setAnnexeCompo(event.target.value);
    this.recrutementExaminateurService
      .sendResultat(data, this.sessionId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
      });
  }

  // startCompo(): void {
  //   const data = {
  //     recrutement_id: this.sessionId,
  //   };
  //   this.errorHandler.startLoader('Démarrage de la composition ...');
  //   this.recrutementExaminateurService
  //     .startCompo(data)
  //     .pipe(this.errorHandler.handleServerErrors())
  //     .subscribe((response) => {
  //       emitAlertEvent(response.message, 'success');
  //       this.errorHandler.stopLoader();
  //     });
  // }
}
