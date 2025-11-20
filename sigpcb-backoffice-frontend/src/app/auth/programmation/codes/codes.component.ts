import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { CompositionService } from 'src/app/core/services/composition.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { KeyValueParam } from 'src/app/helpers/types';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { Agenda } from 'src/app/core/interfaces/examens';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { PdfService } from 'src/app/core/services/pdf.service';
@Component({
  selector: 'app-codes',
  templateUrl: './codes.component.html',
  styleUrls: ['./codes.component.scss'],
})
export class CodesComponent implements OnInit {
  @ViewChild('dataToExport', { static: false })
  public dataToExport!: ElementRef;
  onLoadList = true;
  //Lorsqu'on recupère une annexe
  onFinding = true;
  // Statistique
  onLoadStatique = true;
  onSending = false;
  annexeId: number = 0;
  examenId: number | null = null;
  generationStarted = false;
  progress = 2;
  progressionText = 'Initialisation ....';

  programmations: any = {};

  generated = false;
  alertMessage = null as string | null;
  showResult = false;

  currentAnnexe: AnnexeAnatt | null = null;

  examens: any[] = [];
  annexes: AnnexeAnatt[] = [];
  agenda: Agenda | null = null;
  stats = { total: 0, vague_count: 0 };
  constructor(
    private examenService: ExamenService,
    private errorHandler: HttpErrorHandlerService,
    private composition: CompositionService,
    private annexeService: AnnexeAnattService,
    private pdfService: PdfService
  ) {}

  ngOnInit(): void {
    this.getAnnexeAnatt();
    // L'examen récent
    this._getExamenRecent();
    // Liste des examens
    this.getExamens();
  }

  onAnnexe() {
    if (this.annexeId > 0) {
      this.annexeId = this.annexeId;
      //Lorsque l'annexe est trouvé on récupère les programmations

      this._getAnnexe(() => {
        this.initStat();
        this.getProgrammations();
        this.statistiques();
      });
    } else {
      this.annexeId = 0;
      this.generated = false;
      this.showResult = false;
    }
  }
  generateCompos(): void {
    this.generationStarted = true;
    this.progress = 10;

    this.progressionText = 'Génération en cours ...';
    this.composition
      .generateComposition({
        annexe_id: this.annexeId ?? 0,
        examen_id: this.examenId ?? 0,
      })
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.generationStarted = false;
        })
      )
      .subscribe((response) => {
        this.progress = 40;
        this.alertMessage = `${response.data.total} vague(e) générée (s)`;
        this.errorHandler.emitSuccessAlert(response.message);
        this.distributeIntoSalle();
      });
  }
  /**
   * Fait un appel serveur pour distribuer les candidats dans les salles
   * Elle permet de créer les vagues aussi
   */
  private distributeIntoSalle(): void {
    if (this.canFetch()) {
      this.progressionText = 'Répartition en salle ...';
      this.progress = 43;
      this.composition
        .distributeIntoSalle({
          annexe_id: this.annexeId ?? 0,
          examen_id: this.examenId ?? 0,
        })
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.generationStarted = false;
          })
        )
        .subscribe((response) => {
          this.progress = 80;
          this.errorHandler.emitSuccessAlert(response.message);
          //Récupération de la programmation générée
          this.getProgrammations();
        });
    }
  }

  /**
   * Récupération de la programmation
   */
  private getProgrammations() {
    if (this.canFetch()) {
      this.errorHandler.startLoader('Récupération de la programmation ...');
      this.onLoadList = true;
      const param: KeyValueParam[] = [
        {
          key: 'annexe_id',
          value: this.annexeId,
        },
        {
          key: 'examen_id',
          value: this.examenId,
        },
      ];
      //Lorsque la génération est en cours
      if (this.generationStarted) {
        this.progressionText = 'Récupération des vagues ...';
        this.progress = 70;
      }
      this.composition
        .programmations(param)
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onLoadList = false;
            this.generationStarted = false;
          })
        )
        .subscribe((response) => {
          this.onLoadList = false;
          this.programmations = response.data;
          this.generated = Object.values(response.data).length > 0;

          this.errorHandler.stopLoader();

          if (this.generationStarted) {
            this.progress = 100;
            this.errorHandler.emitSuccessAlert(
              'Programmation des examens générée avec succès'
            );
            this.progressionText = 'Envoi des convocations en cours ...';
            this.errorHandler.startLoader(
              'Envoi des convocations en cours ...'
            );
            this.sendNotifications();
          }
        });
    }
  }

  private statistiques() {
    if (this.canFetch()) {
      this.onLoadStatique = true;
      const param: KeyValueParam[] = [
        {
          key: 'annexe_id',
          value: this.annexeId,
        },
        {
          key: 'examen_id',
          value: this.examenId,
        },
      ];
      this.errorHandler.startLoader('Chargement de la statistique');
      this.composition
        .statistiques(param)
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onLoadStatique = false;
          })
        )
        .subscribe((response) => {
          const data = response.data;
          this.stats = data;
          this.errorHandler.stopLoader();
          this.onLoadStatique = false;
        });
    }
  }
  toggleShowResult() {
    this.showResult = !this.showResult;
    if (this.showResult) {
      this.getProgrammations();
    }
  }

  private getExamens() {
    this.errorHandler.startLoader('Récupération des sessions ...');
    this.examenService
      .getExemens()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examens = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private canFetch() {
    return this.annexeId !== null && this.examenId !== null;
  }

  selectSession(event: any) {
    const sessionId = event.target.value;
    if (sessionId) {
      const currentSession = this.examens.find((e) => e.id == this.examenId);
      if (currentSession) {
        this.agenda = currentSession;
        this.initStat();
        this.getProgrammations();
        this.statistiques();
      }
    }
  }

  downloadAsPdf(): void {
    this.errorHandler.startLoader('Téléchargement en cours');
    this.pdfService
      .programmationCode([
        {
          annexe_id: this.annexeId,
          examen_id: this.examenId,
        },
      ])
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        window.open(response.data, '_blank');
      });
  }

  sendNotifications() {
    this.onSending = true;
    this.composition
      .sendConvocationd({
        annexe_id: this.annexeId,
        examen_id: this.examenId,
      })
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onSending = false;
          this.generationStarted = false;
        })
      )
      .subscribe((response) => {
        this.progress = 100;
        emitAlertEvent(
          'Les convocations sont envoyées avec succès.',
          'success',
          'bottom-right',
          true
        );
        this.errorHandler.stopLoader();
        this.onSending = false;
        this.generationStarted = false;
      });
  }

  private _getAnnexe(call: CallableFunction) {
    if (this.annexeId) {
      this.onFinding = true;

      this.errorHandler.startLoader();
      this.annexeService
        .findById(this.annexeId)
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onFinding = false;
          })
        )
        .subscribe((response) => {
          this.currentAnnexe = response.data;
          call();
          this.errorHandler.stopLoader();
          this.onFinding = false;
        });
    }
  }
  private _getExamenRecent() {
    this.examenService.recentExamen().subscribe((response) => {
      const recent = response.data;
      if (recent) {
        this.agenda = recent;
        this.examenId = this.agenda?.id || 0;
        this.statistiques();
      }
    });
  }

  private initStat() {
    this.stats = {
      total: 0,
      vague_count: 0,
    };
  }

  private getAnnexeAnatt() {
    this.annexeService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.annexes = response.data;
      });
  }
}
