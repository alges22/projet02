import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { Commune } from 'src/app/core/interfaces/commune';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CommuneService } from 'src/app/core/services/commune.service';
import { DepartementService } from 'src/app/core/services/departement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SalleCompoService } from 'src/app/core/services/salle-compo.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { apiUrl, is_array } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-annexe-list',
  templateUrl: './annexe-list.component.html',
  styleUrls: ['./annexe-list.component.scss'],
})
export class AnnexeListComponent {
  annexeanatts: any[] = [];
  annexeanatt = {} as any;
  loadingForList: boolean = false;
  communesdepart = [] as Commune[];
  activateId: number | null = null;

  searchUrl = apiUrl('/annexe-annats');

  onLoading = false;

  noResults = 'Aucune donnée disponible';
  constructor(
    private annexeanattService: AnnexeAnattService,
    private sallecompoService: SalleCompoService,
    private departementService: DepartementService,
    private communeService: CommuneService,
    private errorHandler: HttpErrorHandlerService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.getCommmunesPromise().then(() => {
      this.getAnnexeAnatts();
    });
  }

  private getCommunes() {
    this.errorHandler.startLoader();
    this.loadingForList = true;
    this.communeService
      .getCommunes(-1, 'all')
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.loadingForList = false;
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        this.loadingForList = false;
        if (response.status) {
          this.communesdepart = response.data;
        }
      });
  }

  add() {
    this.router.navigate(['/parametres/territoriales/annexes/add']);
  }

  edit(id: any) {
    this.router.navigate(['/parametres/territoriales/annexes/edit', id]);
  }

  getCommmunesPromise() {
    return new Promise<void>((resolve) => {
      this.getCommunes();
      resolve();
    });
  }

  refresh() {
    this.getAnnexeAnatts();
  }

  private getAnnexeAnatts() {
    // this.annexeanatts = [];
    this.errorHandler.startLoader();
    this.loadingForList = true;
    this.annexeanattService
      .get()
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.loadingForList = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.annexeanatts = response.data;
        }
        this.loadingForList = false;
        this.errorHandler.stopLoader();
      });
  }

  findAnnexeCommuneInCommunes(element: any): any {
    return this.communesdepart.find((item) => item.id === element.commune_id);
  }

  confirmSwitch(data: { id: number; status: boolean }) {
    this.annexeanattService
      .status({ annexe_anatt_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerError('annexes-form'))
      .subscribe((response) => {
        if (response.status) {
          const content = data.status ? 'activé' : 'désactivé';
          this.setAlert(`L'annexe a été ${content} avec succès !`, 'success');
          this.annexeanatts = this.annexeanatts.map((annexeanatt) => {
            if (annexeanatt.id == data.id) {
              annexeanatt.status = data.status;
            }
            return annexeanatt;
          });
        }
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
  // private hideModal() {
  //   this.selectedItems = [];
  //   $(`#${this.modalId}`).modal('hide');
  // }

  onSearches(response: any) {
    if (response.status) {
      this.annexeanatts = response.data.data ?? response.data;
      //Si la réponse n'est pas bonne on reprend les anciennes données
      if (
        !is_array(this.annexeanatts) ||
        (is_array(this.annexeanatts) && this.annexeanatts.length < 1)
      ) {
        this.noResults = 'Aucun résultat trouvé';
      }
    } else {
      this.setAlert(response.message, 'warning', 'middle', true);
    }
    if (response.refresh) {
      this.getAnnexeAnatts();
    }
  }

  destroy(id: number) {
    this.annexeanattService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.getAnnexeAnatts();
        this.setAlert('L`annexe est supprimée avec succès', 'success');
      });
  }
}
