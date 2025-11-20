import { AgrementService } from './../../../core/services/agrement.service';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import {
  AutoEcole,
  Fiche,
  Promoteur,
  Vehicule,
} from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LicenceService } from 'src/app/core/services/licence.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { emitAlertEvent, isDigit } from 'src/app/helpers/helpers';
type Page = 'basic' | 'fiches' | 'recap' | 'end';
@Component({
  selector: 'app-demande-licence',
  templateUrl: './demande-licence.component.html',
  styleUrls: ['./demande-licence.component.scss'],
})
export class DemandeLicenceComponent {
  rejetId: string | null = null;
  auth: any = {};
  moniteurs: Promoteur[] = [];
  vehicules: Vehicule[] = [];
  formData = new FormData();
  moniteurNpi = '';
  vehicule = '';
  page = 'basic' as Page;
  aes: AutoEcole[] = [];
  ae = 0;
  ready = false;
  fiches: Fiche[] = [
    {
      id: '12',
      label: 'Carte grise du ou des véhicule(s)',
      accept: ['.pdf', '.jpg', '.png', 'jpeg'],
      file: null,
      type: 'file',
      required: true,
      name: 'carte_grise',
      multiple: true,
      defaultPath: [],
    },

    {
      id: '13',
      label: 'Assurance visite du ou des véhicule(s)',
      accept: ['.pdf', '.jpg', '.png', 'jpeg'],
      file: null,
      type: 'file',
      required: true,
      name: 'assurance_visite',
      multiple: true,
      defaultPath: [],
    },
    {
      id: '14',
      label: 'Photos du ou des véhicule(s)',
      accept: ['.jpg', '.png', 'jpeg'],
      file: null,
      type: 'img',
      required: true,
      name: 'photo_vehicules',
      multiple: true,
      defaultPath: [],
    },
  ];

  moniteurError: string | null = null;
  moniteurErrors: Promoteur[] = [];
  ficheValides: Fiche[] = [];
  constructor(
    private storage: StorageService,
    private agrementService: AgrementService,
    private errorHandler: HttpErrorHandlerService,
    private authService: AuthService,
    private licenceService: LicenceService,
    private route: ActivatedRoute
  ) {}
  ngOnInit(): void {
    this.auth = this.storage.get('auth') || {};

    this.rejetId = this.route.snapshot.paramMap.get('id');
    if (!this.rejetId) {
      this._getAutoEcoles();
    }

    this._getOldLicence();
  }

  addMoniteur(pressed = false) {
    this.moniteurErrors = [];
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
              if (!response.data.wasMoniteur) {
                this.moniteurErrors.push({
                  prenoms: response.data.prenoms,
                  nom: response.data.nom,
                } as any);
              } else {
                this.moniteurs.push({
                  prenoms: response.data.prenoms,
                  nom: response.data.nom,
                  npi: response.data.npi,
                  telephone: response.data.telephone,
                } as any);
              }
            }

            this.moniteurNpi = '';
            this.errorHandler.stopLoader();
          });
      } else {
        return;
      }
    }
  }
  onFile(file: File | undefined | File[], index: number) {
    if (Array.isArray(file)) {
      this.fiches[index].file = file;
    } else {
      this.fiches[index].file = file;
      if (!file) {
        this.fiches[index].defaultPath = [];
      }
    }
    this.ficheValides = this.fiches.filter((fiche) => !!file);
  }
  fichesValides() {
    return this.fiches.every((fc) => {
      if (fc.required) {
        return !!fc.file || !!fc.defaultPath.length;
      }

      return true;
    });
  }
  removeMoniteur(npi: string) {
    this.moniteurs = this.moniteurs.filter((moniteur) => moniteur.npi != npi);
  }

  addVehicule() {
    const searched = this.vehicules.find(
      (v) => v.immatriculation == this.vehicule
    );
    if (!searched) {
      this.vehicules.push({
        immatriculation: this.vehicule,
      } as Vehicule);
    }

    this.vehicule = '';
  }
  /**
   * Aller à une page donnée
   * @param page
   */
  go(page: Page) {
    this.page = page;
  }

  basicFormValid() {
    const autorisation = this.ae >= 1;

    return (
      autorisation && this.vehicules.length >= 1 && this.moniteurs.length >= 2
    );
  }

  removeVehicule(i: string) {
    this.vehicules = this.vehicules.filter((v) => v.immatriculation != i);
  }

  private _getAutoEcoles() {
    this.errorHandler.startLoader();
    this.agrementService
      .get()
      .pipe(
        this.errorHandler.handleServerErrors((r) => {
          this.ready = true;
        })
      )
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        this.aes = response.data;
        if (this.aes.length == 1) {
          this.ae = this.aes[0].id;
          this.onAe({ value: this.ae });
        }
        this.ready = true;
      });
  }

  onAe(target: any) {
    const id = target.value || 0;
    const ae = this.aes.find((a) => a.id == id);

    if (ae) {
      this.ae = ae.id;
    }
  }

  getAe(id: any) {
    return this.aes.find((a) => a.id == id);
  }
  save() {
    if (this.rejetId) {
      this._update();
    } else {
      this._save();
    }
  }

  private _prepareData() {
    this.formData.append('auto_ecole_id', String(this.ae));
    this.formData.append(
      'moniteurs',
      JSON.stringify(this.moniteurs.map((m) => m.npi))
    );

    for (const fiche of this.ficheValides) {
      if (!!fiche.file) {
        if (fiche.multiple) {
          const files = fiche.file;
          if (Array.isArray(files)) {
            for (const binary of files) {
              this.formData.append(`${fiche.name}[]`, binary);
            }
          }
        } else {
          this.formData.append(fiche.name, fiche.file as any);
        }
      }
    }

    this.formData.append('vehicules', JSON.stringify(this.vehicules));
  }

  private _getOldLicence() {
    if (this.rejetId) {
      this.errorHandler.startLoader();
      this.licenceService
        .rejets(this.rejetId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.aes[0] = response.data.auto_ecole;
          this.ae = this.aes[0].id;
          this.moniteurs = response.data.monitors;
          this.vehicules = JSON.parse(response.data.vehicules);

          const fiche: Record<string, string | undefined> = response.data.fiche;
          for (const f of this.fiches) {
            if (f.name in fiche) {
              let defaultPath = fiche[f.name];
              if (Array.isArray(defaultPath)) {
                f.defaultPath = defaultPath;
              } else {
                f.defaultPath = [defaultPath ?? ''];
              }
            }
          }

          this.ficheValides = this.fiches.filter((f) => !!f.defaultPath);
          this.errorHandler.stopLoader();
        });
    }
  }

  private _save() {
    this.errorHandler.startLoader('Demande de licence en cours ...');
    this._prepareData();
    this.licenceService
      .demande(this.formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.page = 'end';
        this.errorHandler.stopLoader();
      });
  }

  private _update() {
    this.errorHandler.startLoader();
    this.formData.append('demande_rejet_id', this.rejetId || '');
    this._prepareData();
    this.licenceService
      .update(this.formData, this.rejetId || '')
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.page = 'end';
        this.errorHandler.stopLoader();
      });
  }
}
