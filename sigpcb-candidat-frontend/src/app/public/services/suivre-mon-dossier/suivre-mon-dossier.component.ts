import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { TransactionResponse } from 'src/app/core/interfaces/transaction';
import { Candidat } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { DispensePaiementService } from 'src/app/core/services/dispense-paiement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-suivre-mon-dossier',
  templateUrl: './suivre-mon-dossier.component.html',
  styleUrls: ['./suivre-mon-dossier.component.scss'],
})
export class SuivreMonDossierComponent {
  candidat: Candidat | null = null;
  payment: {
    id: string | number;
    uuid: string;
  } | null = null;
  user: any;
  dossier_id: any;
  dossierclose_id: any;
  dossier_candidat_id: any;
  dossier_session_id: any;
  services: any[] = [];
  sessions: any[] = [];
  session = '';
  sessionId = '';
  modalId = 'openModal';
  modalAbandonnerDossier = 'closeDossierModal';
  modalOpenDossier = 'openDossierModal';
  montantButton: any;
  paiement_success: boolean = false;
  montant_payer: any;
  phone_payment: any;
  date_payment: any;
  download_url: any;
  isSessionPayement: boolean = false;
  isloading = false;
  isloadingclosedossier = false;
  isloadingopendossier = false;
  checkoutButtonOptions = {} as any;
  motifPayment = '';
  hasDispensePayment = false;
  cardOpendedIndex: number | null = null;

  openCard(index: number) {
    if (this.cardOpendedIndex === index) {
      this.cardOpendedIndex = null;
    } else {
      this.cardOpendedIndex = index;
    }
  }

  constructor(
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly authService: AuthService,
    private readonly candidatService: CandidatService,
    private readonly router: Router,
    private readonly dispensePaiement: DispensePaiementService
  ) {}

  ngOnInit(): void {
    this.user = this.authService.storageService().get('auth');
    this._getCandidatWithNpi();
    this.checkIfHasDispense(() => {});
    this._getSessions();

    this._getCandidatDossiersParcoursWithId();

    this.checkoutButtonOptions = {
      public_key: environment.fedapay.key,
      environment: environment.fedapay.sandbox,
      transaction: {
        amount: 100,
        description: "Paiement à l'ANaTT du Service ",
      },
      currency: {
        iso: 'XOF',
      },
      onComplete: this.onCheckoutComplete.bind(this),
    };
  }

  private onCheckoutComplete(resp: any) {
    if (resp.reason !== 'DIALOG DISMISSED') {
      if (resp.transaction.status === 'approved') {
        const data = {
          agregateur: 'fedapay',
          description: resp.transaction.description,
          transaction_id: resp.transaction.id,
          reference: resp.transaction.reference,
          mode: resp.transaction.mode,
          operation: resp.transaction.operation,
          transaction_key: resp.transaction.transaction_key,
          montant: resp.transaction.amount,
          phone_payment: resp.transaction.payment_method.number,
          ref_operateur: resp.transaction.transaction_key,
          moyen_payment: 'momo',
          status: resp.transaction.status,
          date_payment: resp.transaction.payment_method.created_at,
          dossier_candidat_id: this.dossier_id,
          session_id: this.sessionId,
        };
        if (this.motifPayment === 'paymentJustif') {
          //@ts-ignore
          data.dossier_session_id = this.dossier_session_id;
          this._savePaiementJustif(data);
        } else if (this.motifPayment === 'paymentExpire') {
          //@ts-ignore
          data.dossier_session_id = this.dossier_session_id;
          this._savePaiementExpire(data);
        } else {
          this._savePaiement(data);
        }
        // this.paiement_success = true;
        this.montant_payer = resp.transaction.amount;
        this.phone_payment = resp.transaction.payment_method.number;
        this.date_payment = resp.transaction.payment_method.created_at;
      }
    }
  }

  private _savePaiement(data: any) {
    this.errorHandler.startLoader();
    this.candidatService
      .savePaimentCandidat(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.paiement_success = false;
        })
      )
      .subscribe((response) => {
        this.paiement_success = true;
        this.download_url = response.data.url;
        this.errorHandler.stopLoader();
      });
  }

  private _savePaiementJustif(data: any) {
    this.errorHandler.startLoader();
    this.candidatService
      .savePaimentCandidatJustif(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.paiement_success = false;
        })
      )
      .subscribe((response) => {
        this.paiement_success = true;
        this.download_url = response.data.url;
        this.errorHandler.stopLoader();
      });
    this.motifPayment = '';
    this.dossier_session_id = '';
  }

  private _savePaiementExpire(data: any) {
    this.errorHandler.startLoader();
    this.candidatService
      .savePaimentCandidatExpire(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.paiement_success = false;
        })
      )
      .subscribe((response) => {
        this.paiement_success = true;
        this.download_url = response.data.url;
        this.errorHandler.stopLoader();
      });
    this.motifPayment = '';
    this.dossier_session_id = '';
  }

  private _getSessions() {
    this.errorHandler.startLoader();
    this.candidatService
      .getSessions()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.sessions = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private _getCandidatWithNpi() {
    if (this.user) {
      this.errorHandler.startLoader();
      this.authService
        .checknpi({ npi: this.user.npi })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.candidat = response.data;
          this.errorHandler.stopLoader();
        });
    }
  }

  private _getCandidatDossiersParcoursWithId() {
    if (this.user) {
      this.errorHandler.startLoader();
      this.candidatService
        .getCandidatDossiersParcoursWithId()
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.services = response.data;
          this.errorHandler.stopLoader();
        });
    }
  }

  statusButtonPaiement(value: any) {
    if (value === '0' || value === '-1') return true;
    else return false;
  }

  statusDuBouton(button: any) {
    const boutonJson = JSON.parse(button);
    if (boutonJson?.status === '0' || boutonJson?.status === '-1') return true;
    else return false;
  }

  slugDuBouton(button: any) {
    if (button) {
      const boutonJson = JSON.parse(button);
      return boutonJson?.bouton;
    } else {
      // Gérer le cas où 'button' est indéfini ou non valide
      return null; // Ou renvoyer une valeur par défaut appropriée
    }
  }

  sessionPaymentModal(
    dossier_id: number,
    montant: any,
    status: any,
    dossier_session_id?: any,
    motif?: any
  ) {
    if (!status) {
      this.dossier_id = dossier_id;
      this.checkoutButtonOptions.transaction.amount = montant;
      if (!motif) this.isSessionPayement = true;
      this.session = '';
      this.motifPayment = motif;
      this.dossier_session_id = dossier_session_id;
      $(`#${this.modalId}`).modal('show');
    }
  }

  sessionModal(
    dossier_candidat_id: number,
    dossier_session_id: number,
    status: any
  ) {
    if (!status) {
      $(`#${this.modalId}`).modal('show');
      this.dossier_candidat_id = dossier_candidat_id;
      this.dossier_session_id = dossier_session_id;
    }
  }

  abandonnerDossier(dossier_id: number, event: Event) {
    event.preventDefault();
    $(`#${this.modalAbandonnerDossier}`).modal('show');
    this.dossierclose_id = dossier_id;
  }

  noCloseDossier() {
    $(`#${this.modalAbandonnerDossier}`).modal('hide');
  }

  yesCloseDossier() {
    this.isloadingclosedossier = true;
    this.errorHandler.startLoader();
    const data = {
      dossier_id: this.dossierclose_id,
    };
    this.candidatService
      .closeDossier(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.errorHandler.stopLoader();
          this.isloadingclosedossier = false;
          $(`#${this.modalAbandonnerDossier}`).modal('hide');
          emitAlertEvent(
            'Le dossier a été bien fermé, vous pouvez vous préinscrire',
            'success'
          );

          setTimeout(() => {
            window.location.reload();
          }, 2000);
        }
      });
  }

  //

  openDossier(dossier_id: number, event: Event) {
    event.preventDefault();
    $(`#${this.modalOpenDossier}`).modal('show');
    this.dossierclose_id = dossier_id;
  }

  noOpenDossier() {
    $(`#${this.modalOpenDossier}`).modal('hide');
  }

  yesOpenDossier() {
    this.isloadingopendossier = true;
    this.errorHandler.startLoader();
    const data = {
      dossier_id: this.dossierclose_id,
    };
    this.candidatService
      .openDossier(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.errorHandler.stopLoader();
          this.isloadingopendossier = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.errorHandler.stopLoader();
          this.isloadingopendossier = false;
          $(`#${this.modalOpenDossier}`).modal('hide');
          emitAlertEvent('Le dossier a été ouvert', 'success');

          setTimeout(() => {
            window.location.reload();
          }, 2000);
        }
      });
  }

  closeDossier(dossier_id: number, status: any) {
    if (!status) {
      this.errorHandler.startLoader();
      const data = {
        dossier_id: dossier_id,
      };
      this._closeDossier(data);
    }
  }

  private _closeDossier(data: any) {
    this.candidatService
      .closeDossier(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        if (response.status) {
          emitAlertEvent(
            'Le dossier a été bien fermé, vous pouvez vous préinscrire',
            'success'
          );
          setTimeout(() => {
            this.router.navigate(['/dashboard/']);
          }, 5000);
        }
      });
  }

  choiceSession() {
    if (this.session) {
      this.isloading = true;

      const data = {
        // dossier_candidat_id: this.dossier_candidat_id,
        dossier_candidat_id: this.dossier_id,
        dossier_session_id: this.dossier_session_id,
        examen_id: parseInt(this.session),
      };

      this._updateSession(data);
    }
  }

  private _updateSession(data: any) {
    this.candidatService
      .updateSession(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          emitAlertEvent(
            'Félicitations, votre choix de session a été enregistré avec succès.',
            'success'
          );
          setTimeout(() => {
            window.location.reload();
          }, 5000);
        }
      });
    this.dossier_session_id = '';
    this.dossier_candidat_id = '';
    this.dossier_id = '';
  }

  reloadPage() {
    window.location.reload();
  }

  updateDossierCandidat(session_id: number) {
    this.router.navigate(['/dashboard/inscription-au-permis/', session_id]);
  }

  // Méthode pour vérifier si une session doit être désactivée
  isSessionDisabled(session: any): boolean {
    const today = new Date().getTime(); // Obtenir le timestamp de la date actuelle en millisecondes
    let dateGestionRejet = new Date(session.fin_gestion_rejet_at).getTime(); // Obtenir le timestamp de la date de gestion de rejet en millisecondes
    // Si le timestamp de la date de gestion de rejet est dépassé par rapport au timestamp de la date actuelle, on désactive la session
    return dateGestionRejet < today;
  }

  // private payment() {
  //   if (this.session) {
  //     this.sessionId = this.session;
  //     $(`#${this.modalId}`).modal('hide');
  //     // @ts-ignore
  //     const FedaPay = window['FedaPay'];
  //     if (FedaPay) {
  //       FedaPay.init(this.checkoutButtonOptions).open();
  //     }
  //   }
  // }

  getTransaction(resp: TransactionResponse) {
    if (resp.status === 'approved') {
      const data = {
        dossier_candidat_id: this.dossier_id,
        session_id: this.sessionId,
      };
      if (this.motifPayment === 'paymentJustif') {
        //@ts-ignore
        data.dossier_session_id = this.dossier_session_id;
        this._savePaiementJustif(data);
      } else if (this.motifPayment === 'paymentExpire') {
        //@ts-ignore
        data.dossier_session_id = this.dossier_session_id;
        this._savePaiementExpire(data);
      } else {
        console.log(resp);
        this.paiement_success = true;
        this.download_url = resp.url;
        this.errorHandler.stopLoader();
      }
      // this.paiement_success = true;
      this.montant_payer = resp.amount;
      this.phone_payment = '';
      this.date_payment = resp.date_payment;
    } else {
      emitAlertEvent(
        "L'envoie de votre demande a échoué, le paiement n'a pu être effectué"
      );
    }
  }

  payNow() {
    if (this.session) {
      this.sessionId = this.session;
      $(`#${this.modalId}`).modal('hide');
      this.errorHandler.startLoader();
      if (!this.hasDispensePayment) {
        this.candidatService
          .savePaimentCandidat({
            session_id: this.sessionId,
          })
          .pipe(
            this.errorHandler.handleServerErrors((response) => {
              this.paiement_success = false;
              this.errorHandler.stopLoader();
            })
          )
          .subscribe((response) => {
            this.errorHandler.stopLoader();
            this.payment = {
              id: response.data.transactionId,
              uuid: response.data.uuid,
            };
          });
      } else {
        this.skipPayment();
      }
    }
  }

  private checkIfHasDispense(call: CallableFunction) {
    this.dispensePaiement
      .check()
      .pipe(
        this.errorHandler.handleServerErrors(() => {
          call();
        })
      )
      .subscribe((response) => {
        const data = response.data;
        if (
          typeof data === 'object' &&
          data !== null &&
          data.status === 'validated'
        ) {
          this.hasDispensePayment = true;
        } else {
          this.hasDispensePayment = false;
        }
        call();
      });
  }

  /**
   * Lorsqu'un candidat obtien un dispense de paiement, il peut choisir sa session sans payer
   */
  skipPayment() {
    this.errorHandler.startLoader('Enregistrement en cours...');
    this.dispensePaiement
      .skipePayment({
        examen_id: this.session,
      })
      .pipe(
        /** En cas d'échec */
        this.errorHandler.handleServerErrors(() => {
          setTimeout(() => {
            window.location.reload();
          }, 3000);
        })
      )
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        this.errorHandler.emitSuccessAlert(response.message);
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      });
  }
  get shouldPay() {
    return this.isSessionPayement && !this.hasDispensePayment;
  }
}
