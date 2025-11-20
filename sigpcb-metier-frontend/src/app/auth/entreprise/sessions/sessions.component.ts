import { Component, ElementRef } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { SessionEntreprise } from 'src/app/core/interfaces/session-entreprise';
import { EntrepriseButtonService } from 'src/app/core/prestation/entreprise-button.service';
import { EntrepriseButton } from 'src/app/core/prestation/interface/entreprise-button';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { EntrepriseService } from 'src/app/core/services/entreprise.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SessionEntrepriseService } from 'src/app/core/services/session-entreprise.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';

@Component({
  selector: 'app-sessions',
  templateUrl: './sessions.component.html',
  styleUrls: ['./sessions.component.scss'],
})
export class SessionsComponent {
  buttons: EntrepriseButton[] = [];
  modalId = 'add-sessions';
  titre_formulaire = "Ajout d'un recrutement";
  modalRejetId = 'rejet-session';
  titre_formulaire_rejet = 'Rejet du recrutement ';
  session = {} as SessionEntreprise;
  sessionRejet: any;
  action: ModalActions = 'store';
  isPostingSession = false;
  isResendSession = false;
  categories: any;
  annexes: any;
  pageNumber = 1;
  sessions: any[] = [];
  onLoadSession = true;

  paginate_data!: any;
  constructor(
    private entrepriseButtonService: EntrepriseButtonService,
    // private authService: AuthService,
    private errorHandler: HttpErrorHandlerService,
    private categoryPermisService: CategoryPermisService,
    private annexeanattService: AnnexeAnattService,
    // private sessionEntrepriseService: SessionEntrepriseService,
    private entrepriseService: EntrepriseService,
    private refElement: ElementRef<HTMLElement>
  ) {}
  ngOnInit(): void {
    // this.user = this.authService.storageService().get('auth');
    // Obtenir les prestationsTemp depuis le service entrepriseButtonService
    this.get();
    this.session.categorie_permis_id = '0' as any;
    this.buttons = this.entrepriseButtonService.getServices();
    this.getCategories();
    this.getAnnexes();
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.get();
  }

  private get() {
    this.onLoadSession = true;
    this.sessions = [];
    this.entrepriseService
      .getSessions(this.pageNumber)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadSession = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.paginate_data = response.data;
          if (this.paginate_data.data) this.sessions = this.paginate_data.data;
        }
        this.onLoadSession = false;
      });
  }

  getCategories() {
    // this.errorHandler.startLoader();
    this.onLoadSession = true;
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
        // this.errorHandler.stopLoader();
        this.onLoadSession = false;
      });
  }

  private getAnnexes() {
    // this.errorHandler.startLoader();
    this.onLoadSession = true;
    this.annexeanattService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.annexes = response.data;
          // this.errorHandler.stopLoader();
          this.onLoadSession = false;
        }
      });
  }

  getCurrentDateTime(): string {
    const now = new Date();
    const year = now.getFullYear();
    const month = ('0' + (now.getMonth() + 1)).slice(-2);
    const day = ('0' + now.getDate()).slice(-2);

    return '';
  }

  transformDate(value: any) {
    const date = new Date(value);
    const year = date.getUTCFullYear();
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const day = String(date.getUTCDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.session = {} as any;
    if (object) {
      this.session = object;
      this.session.date_compo = this.transformDate(object.date_compo);
    }
    this.errorHandler.clearServerErrorsMessages('session-form');
    if (action == 'edit') {
      this.titre_formulaire = `Modifier un recrutement`;
    } else {
      this.titre_formulaire = 'Ajouter un recrutement';
    }

    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  edit(id: any) {
    this.session = this.sessions.find((session) => session.id == id);
    this.openModal('edit', this.session);
    //On fait une réactualisation
    this.entrepriseService
      .findSessionById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.session = response.data;
          this.session.date_compo = this.transformDate(
            response.data.date_compo
          );
        }
      });
  }

  getRejetInfo(id: any) {
    this.errorHandler.startLoader();
    this.entrepriseService
      .findInfoRejetBySessionId(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          console.log(response.data);
          this.sessionRejet = response.data;
          this.errorHandler.stopLoader();
          $(`#${this.modalRejetId}`).modal('show');
          // this.session = response.data;
          // this.session.date_compo = this.transformDate(
          //   response.data.date_compo
          // );
        }
      });
  }

  save(event: Event) {
    event.preventDefault();
    this.isPostingSession = true;
    if (this.session.id) {
      this.updateSession();
    } else {
      this.postSession();
    }
  }

  private postSession() {
    this.entrepriseService
      .postSession(this.session)
      .pipe(
        this.errorHandler.handleServerErrors((response: ServerResponseType) => {
          if (response.message) {
            this.isPostingSession = false;
          }
        }, 'session-form')
      )
      .subscribe((response) => {
        if (response.status) {
          this.hideModal();
          this.setAlert('Le recrutement a été créé avec succès!', 'success');
          this.isPostingSession = false;
        }
        this.get();
      });
  }

  private updateSession() {
    this.entrepriseService
      .updateSession(this.session, this.session.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'session-form',
          (response: ServerResponseType) => {
            this.isPostingSession = false;
          }
        )
      )
      .subscribe((response) => {
        this.get();
        this.isPostingSession = false;
        this.setAlert(response.message, 'success');
        this.hideModal();
      });
  }

  resoumettreSession() {
    this.isResendSession = true;
    const data = {};
    this.entrepriseService
      .resendSession(data, this.sessionRejet?.rejet[0].id ?? 0)
      .pipe(
        this.errorHandler.handleServerErrors((response: ServerResponseType) => {
          if (response.message) {
            this.isResendSession = false;
          }
        }, 'rejet-session-form')
      )
      .subscribe((response) => {
        // if (response.status) {
        // this.hideModal();
        $(`#${this.modalRejetId}`).modal('hide');
        this.setAlert('Le recrutement a été resoumis avec succès!', 'success');
        this.isResendSession = false;
        // }
        this.get();
      });
  }

  private setAlert(
    message: string = '',
    type: AlertType = 'warning',
    position: AlertPosition = 'bottom-right',
    fixed?: boolean
  ) {
    this.errorHandler.emitAlert(message, type, position, fixed);
  }

  /**
   * Clique le button de fermeture de modal
   */
  private hideModal() {
    const modalButton =
      this.refElement.nativeElement?.querySelector<HTMLElement>(
        `[data-bs-dismiss]`
      );
    console.log('hihi');
    if (modalButton) {
      console.log('hoho');
      modalButton.click();
    }
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours');
    this.entrepriseService
      .deleteSession(id)
      .pipe(this.errorHandler.handleServerError('session-form'))
      .subscribe((response) => {
        this.get();
        this.setAlert('Recrutement supprimé avec succès', 'success');
        this.errorHandler.stopLoader();
      });
  }

  showSoumission = false;
  cancelSoumission() {
    this.showSoumission = false;
  }

  openSoumission() {
    this.showSoumission = true;
  }

  confirmSoumission(id: number) {
    const data: any = [];
    this.showSoumission = false;
    this.errorHandler.startLoader('Soumission en cours');
    this.entrepriseService
      .soumissionSession(id, data)
      .pipe(this.errorHandler.handleServerError('session-form'))
      .subscribe((response) => {
        this.get();
        this.setAlert('Recrutement soumis avec succès', 'success');
        this.errorHandler.stopLoader();
      });
  }

  soumission(id: number) {
    const data: any = [];
    this.errorHandler.startLoader('Soumission en cours');
    this.entrepriseService
      .soumissionSession(id, data)
      .pipe(this.errorHandler.handleServerError('session-form'))
      .subscribe((response) => {
        this.get();
        this.setAlert('Recrutement soumis avec succès', 'success');
        this.errorHandler.stopLoader();
      });
  }
}
