import { Component, AfterViewInit } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { AutoecoleService } from 'src/app/core/services/autoecole.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';

@Component({
  selector: 'app-inscription-conduite',
  templateUrl: './inscription-conduite.component.html',
  styleUrls: ['./inscription-conduite.component.scss'],
})
export class InscriptionConduiteComponent implements AfterViewInit {
  autoEcoles: any[] = [];
  centreCompos = ['Cotonou', 'Parakou', 'Ouidah'];
  centreComposSelected = '';
  annexes: any[] = [];
  autoEcole: any;
  quickvInfosPermis: any;
  isloadingSave = false;
  todo: any;
  isValidInfosPermis = false;
  permisList: any[] = [];
  annexeSelected: any = null;
  // autoEcoles: any[] = [];
  userHome: any;
  user: any;
  annexe = '';
  autoEcoleSelected = '';
  isloading = false;
  // Le permis sélectionné
  permisSelected: CategoryPermis | null = null;
  codeAutoEcole: any;
  clearInput: number = 0;
  isAutoEcole: boolean = false;
  codeValidationAutoInput: boolean = false;
  codeValidationAutoInputIsValid: boolean = false;
  isCodeValidationValid: boolean = false;
  dossier_id: any;
  candidat_type: any;
  categorie_permis_id: any;
  nom_permis: any;
  /**
   * Le permis sélectionné
   */

  constructor(
    private errorHandler: HttpErrorHandlerService,
    private cpService: CategoryPermisService,
    private candidatService: CandidatService,
    private annexeanattService: AnnexeAnattService,
    private storage: StorageService,
    private authService: AuthService,
    private autoecoleService: AutoecoleService
  ) {}
  currentPage = 'infos-sur-le-permis';
  onPermisChanged(selected: any) {
    this.permisSelected = selected;
  }
  ngAfterViewInit(): void {
    //@ts-ignore
    this.quickvInfosPermis = new QvForm('#infos-sur-le-permis');
    this.quickvInfosPermis.init();

    this.quickvInfosPermis.onValidate((qvForm: any) => {
      this.isValidInfosPermis = qvForm.passes();
    });
  }

  ngOnInit(): void {
    this._getUserConnected();
    this._getAnnexes();
    // this._getAutoEcoles();
    this.todo = this.storage.get('todo');
    this._getCandidatWithNpi();

    this._getPermisPromise().then(() => {
      return this._getLastDossierCandidatWithID();
    });
  }

  private _getUserConnected() {
    this.userHome = this.authService.storageService().get('auth');
  }

  private _getPermisPromise() {
    return new Promise((resolve, reject) => {
      this.errorHandler.startLoader();
      this.cpService
        .all()
        .pipe(
          this.errorHandler.handleServerErrors((error: any) => {
            this.errorHandler.stopLoader();
            reject(error);
          })
        )
        .subscribe((response) => {
          this.permisList = response.data;
          resolve(response);
          this.errorHandler.stopLoader();
        });
    });
  }

  private _getLastDossierCandidatWithID() {
    if (this.userHome) {
      this.errorHandler.startLoader();
      this.candidatService
        .getLastDossierCandidatWithId()
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          if (
            response.data &&
            response.data.dossier &&
            response.data.dossier_session
          ) {
            this.permisSelected = this.permisList.find(
              (permis) => permis.id == response.data.dossier.categorie_permis_id
            );

            this.annexe = response.data.dossier_session.annexe_id;
            this.dossier_id = response.data.dossier.id;
            this.nom_permis = response.data.nom_permis;
            this.selectAnnexe(this.annexe);
            this.autoEcoleSelected =
              response.data.dossier_session.auto_ecole_id;
            this.autoEcole = this.autoEcoles.find(
              (autoecole) => autoecole.id == this.autoEcoleSelected
            );
            console.log(
              this.autoEcoles,
              this.autoEcoleSelected,
              this.autoEcole
            );
            this.isValidInfosPermis = true;
          }

          this.errorHandler.stopLoader();
        });
    }
  }

  private _getCandidatWithNpi() {
    if (this.userHome) {
      this.errorHandler.startLoader();
      this.authService
        .checknpi({ npi: this.userHome.npi })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.user = response.data;

          this.errorHandler.stopLoader();
        });
    }
  }

  private _getAnnexes() {
    this.errorHandler.startLoader();
    this.annexeanattService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.annexes = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }
  selectAnnexe(annexeId: any): void {
    // Obtenez l'annexe sélectionnée
    if (annexeId) {
      console.log('yes');
      this._getAutoEcoles(annexeId);
    } else {
      console.log('no');
      this.autoEcoleSelected = '';
      this.autoEcoles = [];
      this.codeValidationAutoInput = false;
    }

    // if (annexeId) {
    //   this.annexeSelected = annexeId;
    //   const selectedAnnexe = this.annexes.find(
    //     (annexe) => annexe.id == annexeId
    //   );
    //   this.centreComposSelected = selectedAnnexe.name;

    //   if (selectedAnnexe) {
    //     // Filtrer les auto-écoles ayant des departement_id correspondants dans la propriété annexe_anatt_departements de l'annexe sélectionnée
    //     this.autoEcoles = this.autoEcoles.filter((autoecole) =>
    //       selectedAnnexe.annexe_anatt_departements.some(
    //         (departement: any) =>
    //           autoecole.departement_id == departement.departement_id
    //       )
    //     );
    //   } else {
    //     // Réinitialiser la liste des auto-écoles si aucune annexe n'est sélectionnée
    //     this.autoEcoles = [];
    //   }
    // } else {
    //   // this.isValidInfosPermis = false;
    //   this.autoEcoleSelected = '';
    //   this.autoEcoles = [];
    //   this.codeValidationAutoInput = false;
    // }
    this.isValidInfosPermis = false;
  }

  selectAutoEcole(autoecoleId: any) {
    if (autoecoleId) {
      this.isValidInfosPermis = false;
      this.codeValidationAutoInput = true;
    } else {
      this.codeValidationAutoInput = false;
      this.isValidInfosPermis = false;
    }
    console.log(this.isValidInfosPermis);
  }

  private _getAutoEcoles(annexeId: number) {
    this.errorHandler.startLoader();
    this.candidatService
      .getAutoEcoles(-1, 'all', annexeId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.autoEcoles = response.data;
          console.log(this.autoEcoles);
          this.errorHandler.stopLoader();
        }
      });
  }

  private _getPermis() {
    this.errorHandler.startLoader();
    this.cpService
      .all()
      .pipe(
        this.errorHandler.handleServerErrors((error: any) => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        this.permisList = response.data;
        this.errorHandler.stopLoader();
      });
  }

  onInputValid(data: { isValid: boolean; values: (number | null)[] }) {
    console.log(data.isValid);
    if (data.isValid) {
      this.codeAutoEcole = data.values.join('');
      this.isloading = true;
      this.autoecoleService
        .findByCode(this.codeAutoEcole)
        .pipe(
          this.errorHandler.handleServerErrors((error: any) => {
            this.isloading = false;
            this.codeAutoEcole = null;
            this.clearInput++;
            this.isValidInfosPermis = false;
          })
        )
        .subscribe((response) => {
          this.isloading = false;
          this.autoEcole = response.data;
          if (this.autoEcole)
            if (this.autoEcoleSelected == this.autoEcole.id) {
              this.isAutoEcole = true;
              this.isValidInfosPermis = true;
            } else {
              this.errorHandler.emitAlert(
                "Ce code ne correspond à l'auto école sélectionnée",
                'danger',
                'middle',
                true
              );
              this.isValidInfosPermis = false;
            }

          this.clearInput++;
        });
    } else {
      this.codeAutoEcole = null;
      this.codeValidationAutoInputIsValid = false;
      this.isValidInfosPermis = false;
    }
  }

  changeAutoEcoleValidation() {
    if (this.codeValidationAutoInput) {
      console.log(this.isAutoEcole);
      if (this.isAutoEcole) {
        this.isValidInfosPermis = true;
        return true;
      } else {
        // this.isValidInfosPermis = false;
        return false;
      }
    } else {
      return true;
    }
  }

  /**
   * Vérifier si la page d'informations sur le permis est valide
   * @returns
   */
  inforPermisPageIsValid() {
    return (
      // this.isAutoEcole &&
      this.isValidInfosPermis &&
      this.permisSelected !== undefined &&
      this.permisSelected !== null &&
      typeof this.permisSelected === 'object'
      // &&
      // this.changeAutoEcoleValidation()
      // &&
      // this.isCodeValidationValid
    );
  }

  goto(page: string, event: Event) {
    if (page == 'completed') {
      this.save(event);
    }
  }
  save(event: Event) {
    event.preventDefault();
    this.candidat_type = this.storage.get('userType');
    const formData = new FormData();
    this.categorie_permis_id = this.permisSelected?.id;
    formData.append('auto_ecole_id', this.autoEcoleSelected);
    formData.append('dossier_candidat_id', this.dossier_id);
    formData.append('categorie_permis_id', this.categorie_permis_id);
    formData.append('annexe_anatt_id', this.annexe);
    formData.append('examen_type', 'conduite');
    formData.append('nom_permis', this.nom_permis);
    this.isloadingSave = true;
    this.post(formData);
  }

  private post(data: any) {
    this.candidatService
      .postParcoursCandidat(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloadingSave = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.currentPage = 'completed';
          this.isloadingSave = false;
        }
      });
  }
}
