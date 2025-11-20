import { Component } from '@angular/core';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { CompoRecrutementService } from 'src/app/core/services/compo-recrutement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutemmentExaminateurService } from 'src/app/core/services/recrutemment-examinateur.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { Epreuve } from './epreuve';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';

@Component({
  selector: 'app-conduites',
  templateUrl: './conduites.component.html',
  styleUrls: ['./conduites.component.scss'],
})
export class ConduitesComponent {
  epreuve = {} as Epreuve;
  onLoadingEpreuve = false;
  isPostingEpreuve = false;
  isPostingAnnotation = false;
  modalId = 'epreuve';
  modalAnnotationId = 'annotation';
  action: ModalActions = 'store';
  epreuve_formulaire = "Ajout d'une épreuve";
  annexeId: any;
  sessions: any[] = [];
  candidats: any[] = [];
  sessionId: any;
  epreuves: any[] = [];
  annotation: any;
  candidat: any;
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
      this.getCandidatsBySessionIdEntreprise(this.sessionId);
    }
  }

  getCandidatsBySessionIdEntreprise(sessionId: any) {
    this.errorHandler.startLoader('Récupération des candidats ...');
    this.recrutementExaminateurService
      .getCandidatsBySessionIdEntreprise(sessionId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.candidats = response.data;
        this.getEpreuve();
        this.errorHandler.stopLoader();
      });
  }

  openModal(action: ModalActions, object?: any) {
    this.epreuve = {} as Epreuve;
    if (object) {
      this.epreuve = object;
    }
    if (action == 'edit') {
      this.epreuve_formulaire = "Modification d'épreuve";
    } else {
      this.epreuve_formulaire = 'Ajouter une épreuve';
    }

    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  getEpreuve() {
    // this.errorHandler.startLoader();
    this.onLoadingEpreuve = true;
    this.epreuves = [];
    this.recrutementExaminateurService
      .getEpreuve(this.sessionId)
      .pipe(this.errorHandler.handleServerError('epreuve-conduite-form'))
      .subscribe((response) => {
        this.epreuves = response.data;
        // this.errorHandler.stopLoader();
        this.onLoadingEpreuve = false;
      });
  }

  edit(id: any) {
    this.epreuve = this.epreuves.find((epreuve) => epreuve.id == id);
    this.openModal('edit', this.epreuve);
    // Actualisation
    this.recrutementExaminateurService
      .findEpreuveById(id)
      .pipe(this.errorHandler.handleServerError('admin-titres-form'))
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.epreuve = response.data;
        }
      });
  }

  save(event: Event) {
    event.preventDefault();
    this.isPostingEpreuve = true;
    this.epreuve.recrutement_id = this.sessionId;
    if (this.epreuve.id) {
      this.updateEpreuve();
    } else {
      this.postEpreuve();
    }
  }

  postEpreuve() {
    this.errorHandler.clearServerErrorsMessages('epreuve-conduite-form');
    this.recrutementExaminateurService
      .postEpreuve(this.epreuve)
      .pipe(
        this.errorHandler.handleServerError(
          'epreuve-conduite-form',
          (response) => {
            this.isPostingEpreuve = false;
          }
        )
      )
      .subscribe((response) => {
        this.getEpreuve();
        this.setAlert(response.message, 'success');
        this.hideModal();
        this.isPostingEpreuve = false;
      });
  }

  private updateEpreuve() {
    this.recrutementExaminateurService
      .updateEpreuve(this.epreuve, this.epreuve.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'epreuve-conduite-form',
          (response) => {
            this.isPostingEpreuve = false;
          }
        )
      )
      .subscribe((response) => {
        this.getEpreuve();
        this.setAlert(response.message, 'success');
        this.isPostingEpreuve = false;
        this.hideModal();
      });
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours ...');
    this.recrutementExaminateurService
      .deleteEpreuve(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.getEpreuve();
        this.setAlert('Epreuve supprimée avec succès', 'success');
        this.errorHandler.stopLoader();
      });
  }

  sendConvocation(): void {
    const data = {};
    this.errorHandler.startLoader('Envoi des convocations ...');
    // this.composition.setAnnexeCompo(event.target.value);
    this.recrutementExaminateurService
      .sendConvocation(data, this.sessionId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
      });
  }

  stopCompo(): void {
    const data = {};
    this.errorHandler.startLoader('Arrêt de la composition ...');
    // this.composition.setAnnexeCompo(event.target.value);
    this.recrutementExaminateurService
      .stopCompo(data, this.sessionId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
      });
  }

  startCompo(): void {
    const data = {
      recrutement_id: this.sessionId,
    };
    this.errorHandler.startLoader('Démarrage de la composition ...');
    this.recrutementExaminateurService
      .startCompo(data)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
      });
  }

  annoter(id: any) {
    const c = this.candidats.find((candidat) => candidat.id == id);
    this.candidat = c?.candidat_info;
    this.candidat.id = c.id;
    this.candidat.recrutement_id = parseInt(this.sessionId);
    this.candidat.epreuves = this.epreuves.map((epreuve) => ({
      recrutement_epreuve_id: epreuve.id,
      name: epreuve.name,
      note: '',
    }));
    $(`#${this.modalAnnotationId}`).modal('show');
  }

  valider(): void {
    this.isPostingAnnotation = true;
    this.recrutementExaminateurService
      .annotation(this.candidat)
      .pipe(
        this.errorHandler.handleServerError('annotation-form', (response) => {
          this.isPostingAnnotation = false;
        })
      )
      .subscribe((response) => {
        // this.getEpreuve();
        this.getCandidatsBySessionIdEntreprise(this.sessionId);
        this.setAlert(response.message, 'success');
        this.hideAnnotationModal();
        this.isPostingAnnotation = false;
      });
  }

  /**
   * Ferme le modal
   */
  private hideModal() {
    $(`#${this.modalId}`).modal('hide');
  }

  private hideAnnotationModal() {
    $(`#${this.modalAnnotationId}`).modal('hide');
  }

  private setAlert(
    message: string = '',
    type: AlertType = 'warning',
    position: AlertPosition = 'bottom-right',
    fixed?: boolean
  ) {
    this.errorHandler.emitAlert(message, type, position, fixed);
  }
}
