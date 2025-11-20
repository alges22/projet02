import { Component } from '@angular/core';
import { Agregateur } from 'src/app/core/interfaces/agregateur';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { AgregateurService } from 'src/app/core/services/agregateur.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { asset } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-agregateur',
  templateUrl: './agregateur.component.html',
  styleUrls: ['./agregateur.component.scss'],
})
export class AgregateurComponent {
  agregateurs: any[] = [];

  agregateur = {} as Agregateur;

  modalId = 'agregateur';

  agregateur_formulaire = "Ajout d'un agrégateur";

  activateId: number | null = null;

  onDeleting = false;

  onLoading = false;

  action: ModalActions = 'store';

  private photo: File | null = null;

  constructor(
    private agregateurService: AgregateurService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
  }
  refresh() {
    this.get();
  }
  post() {
    const formData = new FormData();
    formData.append('photo', this.photo ?? '');
    formData.append('name', this.agregateur.name ?? '');
    formData.append('status', Boolean(this.agregateur.status) ? '1' : '0');
    this.agregateurService
      .post(formData)
      .pipe(
        this.errorHandler.handleServerError('agregateur-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.get();
        this.hideModal();
        this.setAlert('Agrégateur ajouté avec succès!', 'success');
        this.onLoading = false;
      });
  }

  get() {
    this.errorHandler.startLoader();
    this.agregateurService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.agregateurs = response.data;
        this.errorHandler.stopLoader();
      });
  }

  /**
   * Clique le button de fermeture de modal
   */
  private hideModal() {
    $(`#${this.modalId}`).modal('hide');
  }

  private setAlert(
    message: string = '',
    type: AlertType = 'warning',
    position: AlertPosition = 'bottom-right',
    fixed?: boolean
  ) {
    this.errorHandler.emitAlert(message, type, position, fixed);
  }

  openModal(action: ModalActions, object?: any) {
    this.agregateur = {} as Agregateur;
    this.errorHandler.clearServerErrorsMessages('agregateur-form');
    if (object) {
      this.agregateur = object;
    }
    if (action == 'edit') {
      this.agregateur_formulaire = 'Formulaire de modification';
    } else {
      this.agregateur_formulaire = 'Ajouter un agrégateur';
    }

    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;

    if (this.agregateur.id) {
      this.update();
    } else {
      this.post();
    }
  }
  private update() {
    this.agregateur.status = (this.agregateur.status as any) == '1';
    const formData = new FormData();
    formData.append('photo', this.photo ?? '');
    formData.append('name', this.agregateur.name ?? '');
    formData.append('status', this.agregateur.status ? '1' : '0');
    this.agregateurService
      .update(formData, this.agregateur.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError('agregateur-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.get();
        this.onLoading = false;
        this.setAlert(response.message, 'success');
        this.hideModal();
      });
  }
  edit(id: any) {
    this.agregateur = this.agregateurs.find((ua) => ua.id == id);

    this.openModal('edit', this.agregateur);

    // Actualisation
    this.agregateurService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.agregateur = response.data;
        }
      });
  }

  confirmSwitch(data: { id: number; status: boolean }) {
    this.agregateurService
      .status({ agregateur_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const content = data.status ? 'activé' : 'désactivé';
        this.setAlert(`L'agrégateur a été ${content} avec succès !`, 'success');
      });
  }

  destroy(agregateur_id: number) {
    this.errorHandler.startLoader();
    this.agregateurService
      .delete(agregateur_id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert('Agrégateur supprimé avec succès', 'success');
      });
  }

  onFileChange(event: any) {
    const file = event.target.files[0];

    if (file) {
      this.photo = file;
    }
  }
  getPhoto(agregateur: any) {
    return asset(agregateur.photo);
  }
}
