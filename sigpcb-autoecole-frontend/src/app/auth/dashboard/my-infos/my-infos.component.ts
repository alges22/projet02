import {
  AfterViewChecked,
  AfterViewInit,
  Component,
  Input,
  OnInit,
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { trim } from 'jquery';
import {
  Promoteur,
  Fiche,
  DemandeAgrement,
  OTPEvent,
  AutoEcole,
} from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { CommuneService } from 'src/app/core/services/commune.service';
import { DemandeAgrementService } from 'src/app/core/services/demande-agrement.service';
import { DepartementService } from 'src/app/core/services/departement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { UsersService } from 'src/app/core/services/users.service';
import { isDigit, emitAlertEvent } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';
type Page = 'basic' | 'monitors' | 'recap' | 'end';
@Component({
  selector: 'app-my-infos',
  templateUrl: './my-infos.component.html',
  styleUrls: ['./my-infos.component.scss'],
})
export class MyInfosComponent
  implements OnInit, AfterViewChecked, AfterViewInit
{
  page = 'recap' as Page;
  @Input() promoteur: Promoteur | null = null;
  rejetId: string | null = null;
  moniteurs: Promoteur[] = [];

  moniteurError: string | null = null;
  form = {} as AutoEcole;
  departements: any[] = [];
  communes: any[] = [];
  allCommunes: any[] = [];
  ifuTab: number[] = [];
  formData = new FormData();
  moniteurNpi = '';
  inputIsValid = false;
  auth = false;

  constructor(
    private communeService: CommuneService,
    private departementService: DepartementService,
    private errorHandler: HttpErrorHandlerService,
    private storage: StorageService,
    private authService: AuthService,
    private userService: UsersService,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this._getCommunes();
    this._getDepartements();

    if (this.authService.checked()) {
      this.auth = true;
      this.promoteur = this.storage.get('auth');
    }
    this.rejetId = this.route.snapshot.paramMap.get('rejetId');
    if (this.rejetId) {
      this.page = 'basic';
      this._getFromRejet();
    } else {
      this.userService
        .myInfos()
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          const data = response.data;

          this.form = data;
          this.moniteurs = data.monitors;

          this.errorHandler.stopLoader();
        });
    }
  }

  private _getFromRejet() {
    this.userService
      .getRejetsInfos(this.rejetId ?? '')
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;

        this.form = data;
        this.moniteurs = data.monitors;

        this.errorHandler.stopLoader();
      });
  }
  ngAfterViewInit(): void {}

  onDepartement(target: any): void {
    const depId = Number(target.value);

    if (isNaN(depId) || depId == 0) {
      this.form.departement_id = 0;
      this.communes = this.allCommunes;
    } else {
      this.form.departement_id = depId;
      this.communes = this.allCommunes.filter((commune) => {
        return commune.departement_id == depId;
      });
    }
  }

  onCommune(target: any): void {
    const communeId = Number(target.value);

    if (isNaN(communeId) || communeId == 0) {
      this.form.commune_id = 0;
    } else {
      this.form.commune_id = communeId;

      const commune = this.allCommunes.find((c) => c.id == communeId);
      if (commune) {
        const dep = this.departements.find((dep) => {
          return dep.id == commune.departement_id;
        });

        if (dep) {
          this.form.departement_id = dep.id;
        }
      }
    }
  }

  /**
   * Aller à une page donnée
   * @param page
   */
  go(page: Page) {
    this.page = page;
  }

  private _getDepartements() {
    this.errorHandler.startLoader();
    this.departementService.getDepartements().subscribe((response) => {
      this.departements = response.data;
      this.errorHandler.stopLoader();
    });
  }

  private _getCommunes() {
    this.errorHandler.startLoader();
    this.communeService.getCommunes().subscribe((response) => {
      this.allCommunes = response.data;
      this.allCommunes = this.allCommunes.map((item) => {
        item.name = `${item.name} / ${item.departement.name}`;
        return item;
      });

      this.communes = this.allCommunes;
      this.errorHandler.stopLoader();
    });
  }
  ngAfterViewChecked(): void {}

  addMoniteur(pressed = false) {
    const npis = this.moniteurNpi.split('');
    if (npis.some((i) => !isDigit(i))) {
      this.moniteurError = 'Le numéro NPI est incorrect';
      return;
    } else {
      this.moniteurError = null;
    }

    if (pressed && this.moniteurNpi.length != 10) {
      this.moniteurError = 'Le numéro NPI est trop court';
    }

    if (this.moniteurNpi.length == 10) {
      const searched = this.moniteurs.find(
        (moniteur) => moniteur.npi == this.moniteurNpi
      );
      if (!searched) {
        this.errorHandler.startLoader('Vérification du NPI ...');
        this.authService
          .npi({
            npi: this.moniteurNpi,
          })
          .pipe(this.errorHandler.handleServerErrors())
          .subscribe((response) => {
            if (!searched) {
              this.moniteurs.push({
                prenoms: response.data.prenoms,
                nom: response.data.nom,
                npi: response.data.npi,
                telephone: response.data.telephone,
              } as any);
            }

            this.moniteurNpi = '';
            this.errorHandler.stopLoader();
          });
      } else {
        return;
      }
    }
  }

  onKeydown(event: any) {
    if (event.keyCode === 13) {
      this.addMoniteur();
    }
  }
  removeMoniteur(npi: string) {
    this.moniteurs = this.moniteurs.filter((moniteur) => moniteur.npi != npi);
  }

  basicFormValid() {
    const autoEcoleValid = !!this.form.name && this.form.name.length > 1;

    const communeIdValid = this.form.commune_id != 0;
    const departementIdValid = this.form.departement_id != 0;

    return autoEcoleValid && communeIdValid && departementIdValid;
  }

  private __prepareData() {
    const form: any = this.form;

    for (const name in form) {
      if (Object.prototype.hasOwnProperty.call(form, name)) {
        const value = form[name];
        if (!!value) {
          this.formData.append(name, trim(value));
        }
      }
    }
    this.formData.append(
      'moniteurs',
      JSON.stringify(this.moniteurs.map((m) => m.npi))
    );

    this.formData.append('npi', this.promoteur?.npi || '');
    this.formData.append('vehicules', '[]');
  }

  save() {
    this.__prepareData();
    this.errorHandler.startLoader();
    this.userService
      .updateAeInfos(this.formData, this.rejetId || '')
      .pipe(this.errorHandler.handleServerErrors((response) => {}))
      .subscribe((response) => {
        this.page = 'end';
        this.errorHandler.stopLoader();
      });
  }
}
