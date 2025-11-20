import { Component, ElementRef } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { CandidatEntreprise } from 'src/app/core/interfaces/candidat-entreprise';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { SessionEntreprise } from 'src/app/core/interfaces/session-entreprise';
import { EntrepriseButtonService } from 'src/app/core/prestation/entreprise-button.service';
import { EntrepriseButton } from 'src/app/core/prestation/interface/entreprise-button';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { EntrepriseService } from 'src/app/core/services/entreprise.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-session',
  templateUrl: './session.component.html',
  styleUrls: ['./session.component.scss'],
})
export class SessionComponent {
  buttons: EntrepriseButton[] = [];
  modalId = 'add-sessions';
  titre_formulaire = "Inscription d'un candidat";
  session = {} as SessionEntreprise;
  candidat = {} as CandidatEntreprise;
  action: ModalActions = 'store';
  isPostingCandidat = false;
  categories: any;
  langues: any[] = [];
  dossierIndex: number | null = null;
  restrictions: any[] = [
    {
      id: 0,
      name: 'Aucune',
      description: null,
      created_at: null,
      updated_at: null,
    },
  ];
  piecepermis = {
    label: 'Joindre votre permis',
    file: undefined as undefined | File,
    name: 'permis',
    content: 'Permis de conduire',
    src: '',
  };
  fichierpermis: any;
  pageNumber = 1;
  candidats: any[] = [];
  onLoadCandidat = true;
  sessionId: any;

  paginate_data!: any;
  constructor(
    private entrepriseButtonService: EntrepriseButtonService,
    // private authService: AuthService,
    private errorHandler: HttpErrorHandlerService,
    private categoryPermisService: CategoryPermisService,
    private entrepriseService: EntrepriseService,
    private route: ActivatedRoute,
    private refElement: ElementRef<HTMLElement>
  ) {}
  ngOnInit(): void {
    // this.user = this.authService.storageService().get('auth');
    // Obtenir les prestationsTemp depuis le service entrepriseButtonService
    this.route.params.subscribe((params) => {
      const id = params['sesionId'];
      this.sessionId = id;
      // this.candidat.recrutement_id = id;
      this.getCandidatsBySessionId(id);
    });
    this.session.categorie_permis_id = '0' as any;
    this.buttons = this.entrepriseButtonService.getServices();
    this.getCategories();
    this.getLangues();
    this.getRestrictions();
  }

  showDossier(i: number): void {
    if (this.dossierIndex === i) {
      this.dossierIndex = null;
    } else {
      this.dossierIndex = i;
    }
  }

  private getCandidatsBySessionId(sessionId: number) {
    this.onLoadCandidat = true;
    this.candidats = [];
    this.entrepriseService
      .getCandidats(sessionId, this.pageNumber)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadCandidat = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.paginate_data = response.data;
          if (this.paginate_data.data) this.candidats = this.paginate_data.data;
        }
        this.onLoadCandidat = false;
      });
  }

  edit(id: any) {
    this.candidat = this.candidats.find((candidat) => candidat.id == id);
    this.openModal('edit', this.candidat);
    //On fait une réactualisation
    this.entrepriseService
      .getCandidatById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.candidat = response.data;
        }
      });
  }

  downloadConvocation(id: any) {
    this.errorHandler.startLoader();
    //On fait une réactualisation
    this.entrepriseService
      .downloadConvocationByCandidatId(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        console.log(response.data);
        this.errorHandler.stopLoader();
      });
  }

  asset(path: string) {
    return environment.endpoints.asset + path;
  }

  base_url(path: string) {
    return environment.entreprise.base_url + path;
  }
  getCategories() {
    this.errorHandler.startLoader();
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private getLangues() {
    this.errorHandler.startLoader();
    this.entrepriseService
      .getLangues()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          // Filtrer les langues dont le statut est true
          this.langues = response.data.filter(
            (langue: any) => langue.status === true
          );
          this.errorHandler.stopLoader();
        }
      });
  }

  private getRestrictions() {
    this.errorHandler.startLoader();
    this.entrepriseService
      .getRestrictions()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.restrictions = [...this.restrictions, ...response.data];
          this.errorHandler.stopLoader();
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

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.candidat = {} as any;
    if (object) {
      this.candidat = object;
    }
    this.errorHandler.clearServerErrorsMessages('session-form');
    if (action == 'edit') {
      // this.titre_formulaire = `Modification: ${this.admin.first_name} ${this.admin.last_name}`;
    } else {
      this.titre_formulaire = "Inscription d'un candidat";
    }

    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  onFilePermisPrealableChange(file: File | undefined) {
    if (file) {
      this.piecepermis.file = file;
      if (file && file.type.startsWith('image/')) {
        this.piecepermis.src = URL.createObjectURL(file);
      }
    }
  }

  save(event: Event) {
    event.preventDefault();
    this.fichierpermis = this.piecepermis.file;
    const formData = new FormData();
    formData.append('npi', this.candidat.npi);
    formData.append('recrutement_id', this.sessionId);
    formData.append('num_permis', this.candidat.num_permis);
    formData.append('langue_id', this.candidat.langue_id);
    if (this.fichierpermis) {
      formData.append('permis_file', this.fichierpermis);
    }

    this.isPostingCandidat = true;
    if (this.candidat.id) {
      this.updateCandidat(formData);
    } else {
      this.postCandidat(formData);
    }
  }

  private postCandidat(data: any) {
    this.entrepriseService
      .postCandidat(data)
      .pipe(
        this.errorHandler.handleServerErrors((response: ServerResponseType) => {
          if (response.message) {
            this.isPostingCandidat = false;
          }
        }, 'candidat-form')
      )
      .subscribe((response) => {
        if (response.status) {
          this.hideModal();
          this.setAlert('Le candidat a été inscrit avec succès!', 'success');
          this.isPostingCandidat = false;
        }
        this.getCandidatsBySessionId(this.sessionId);
      });
  }

  private updateCandidat(data: any) {
    this.entrepriseService
      .updateCandidat(data, this.candidat.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'candidat-form',
          (response: ServerResponseType) => {
            this.isPostingCandidat = false;
          }
        )
      )
      .subscribe((response) => {
        this.getCandidatsBySessionId(this.sessionId);
        this.isPostingCandidat = false;
        this.setAlert(response.message, 'success');
        this.hideModal();
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
    if (modalButton) {
      modalButton.click();
    }
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.getCandidatsBySessionId(this.sessionId);
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours');
    this.entrepriseService
      .deleteCandidat(id)
      .pipe(this.errorHandler.handleServerError('candidat-form'))
      .subscribe((response) => {
        this.getCandidatsBySessionId(this.sessionId);
        this.setAlert('Session supprimée avec succès', 'success');
        this.errorHandler.stopLoader();
      });
  }
}
