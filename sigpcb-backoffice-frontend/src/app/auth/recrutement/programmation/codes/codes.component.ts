import { Component } from '@angular/core';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CompoRecrutementService } from 'src/app/core/services/compo-recrutement.service';
import { ExaminateurService } from 'src/app/core/services/examinateur.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutemmentExaminateurService } from 'src/app/core/services/recrutemment-examinateur.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-codes',
  templateUrl: './codes.component.html',
  styleUrls: ['./codes.component.scss'],
})
export class CodesComponent {
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
      const states = ['validate'];
      this.errorHandler.startLoader('Récupération des candidats ...');
      // this.composition.setAnnexeCompo(event.target.value);
      this.recrutementExaminateurService
        .getCandidatsBySessionIdEntreprise(event.target.value)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          console.log(response.data);
          this.candidats = response.data;
          this.errorHandler.stopLoader();
        });
    }
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

  // getMonitoring() {
  //   const filters: any = [
  //     { list: 'pending' },
  //     { page: this.pageNumber },
  //     { annexe_id: this.filters.annexeSelected },
  //     { categorie_permis_id: this.filters.permisSelected },
  //     { examen_id: this.filters.sessionSelected, search: this.filters.search },
  //   ];
  //   this.onLoadMonitoring = true;
  //   this.monitoringService
  //     .all(filters)
  //     .pipe(
  //       this.errorHandler.handleServerErrors((response) => {
  //         this.onLoadMonitoring = false;
  //       })
  //     )
  //     .subscribe((response) => {
  //       const data = response.data;
  //       this.paginate_data = data.paginate_data;
  //       this.monitoring = this.paginate_data.data;
  //       this.onLoadMonitoring = false;
  //     });
  // }
}
