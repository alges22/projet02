import { Component, ElementRef, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import domToImage from 'dom-to-image';
import jsPDF from 'jspdf';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { FullMonth } from 'src/app/core/interfaces/date';
import { Agenda } from 'src/app/core/interfaces/examens';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CompositionService } from 'src/app/core/services/composition.service';
import { DateService } from 'src/app/core/services/date.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PdfService } from 'src/app/core/services/pdf.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { KeyValueParam } from 'src/app/helpers/types';

@Component({
  selector: 'app-conduites',
  templateUrl: './conduites.component.html',
  styleUrls: ['./conduites.component.scss'],
})
export class ConduitesComponent {
  @ViewChild('dataToExport', { static: false })
  public dataToExport!: ElementRef;
  onLoadList = true;

  annexeId: number = 0;
  examenId: number | null = null;
  generationStarted = false;
  progress = 2;
  progressionText = 'Initialisation ....';
  onSending = false;

  currentSession = {
    mois: null as FullMonth | null,
    annee: 2023,
    programmations: {},
    generated: false,
    session_long: '',
  };

  alertMessage = null as string | null;
  showResult = false;

  currentAnnexe: AnnexeAnatt | null = null;
  annexes: AnnexeAnatt[] = [];
  examens: any[] = [];

  agenda: Agenda | null = null;
  candidatsApts: any[] = [];
  constructor(
    private examenService: ExamenService,
    private errorHandler: HttpErrorHandlerService,
    private composition: CompositionService,
    private annexeService: AnnexeAnattService,
    private dateService: DateService,
    private route: ActivatedRoute,
    private pdfService: PdfService
  ) {}

  ngOnInit(): void {
    this.getAnnexeAnatt();
  }

  generateCompos(): void {
    this.generationStarted = true;
    this.progress = 10;

    this.progressionText = 'Génération en cours ...';
    this.composition
      .generateConduite({
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
        this.juriesDistrution();
      });
  }

  downloadAsPdf(): void {
    this.errorHandler.startLoader('Téléchargement en cours');
    this.pdfService
      .programmationConduite([
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
  /**
   * Fait un appel serveur pour distribuer les candidats dans les salles
   * Elle permet de créer les vagues aussi
   */
  private juriesDistrution(): void {
    if (this.canFetch()) {
      this.progressionText = 'Répartition des juries ...';
      this.progress = 43;
      this.composition
        .juriesDistrution({
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
          //
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
        .programmationsConduite(param)
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onLoadList = false;
            this.generationStarted = false;
          })
        )
        .subscribe((response) => {
          this.onLoadList = false;
          const data = Object.keys(response.data);
          if (data.length) {
            this.currentSession.programmations = response.data;
            this.currentSession.generated = true;
            if (Object.values(response.data).length) {
              this.showResult = true;
            }
            this.errorHandler.stopLoader();
            if (this.generationStarted) {
              this.progress = 100;
              this.errorHandler.emitSuccessAlert(
                'Programmation des examens générée avec succès'
              );
              this.errorHandler.startLoader(
                'Envoi de la convocation en cours ...'
              );
              this.sendNotifications();
            }
          } else {
            this.generationStarted = false;
            this.errorHandler.stopLoader();
          }
        });
    }
  }

  private fill(data: any): void {
    this.currentSession.mois = data.mois;
    this.currentSession.annee = data.annee;
    this.currentSession.session_long = data.session_long;
    this.examenId = data.id;
    this.getCandidatsApts();
  }

  private getCandidatsApts() {
    if (this.canFetch()) {
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
      this.composition
        .getCandidatsConduite(param)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          const data = response.data;
          this.candidatsApts = data;
        });
    }
  }
  toggleShowResult() {
    this.showResult = !this.showResult;
  }

  private fresh() {
    this.getCandidatsApts();
  }

  private getExamens(call: CallableFunction) {
    this.errorHandler.startLoader('Récupération de la session courante ...');
    this.examenService
      .getExemens()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examens = response.data;
        const mois = this.dateService.getFullMonthNow();
        const currentSession = this.examens.find(
          (e) =>
            (e.mois as string).toLocaleLowerCase() === mois.toLocaleLowerCase()
        );
        if (currentSession) {
          this.agenda = currentSession;
          this.fill(currentSession);
          call();
        }
        this.errorHandler.stopLoader();
      });
  }

  private canFetch() {
    return this.annexeId !== null && this.examenId !== null;
  }

  selectSession(event: any) {
    this.currentSession.generated = false;
    this.showResult = false;
    this.candidatsApts = [];
    const sessionId = event.target.value;
    if (sessionId) {
      const currentSession = this.examens.find((e) => e.id == sessionId);
      if (currentSession) {
        this.agenda = currentSession;
        this.fill(currentSession);
        // this.fresh();
        this.getProgrammations();
      }
    }
  }

  private downloadAsPdfOld(): void {
    const width = this.dataToExport.nativeElement.clientWidth;
    const height = this.dataToExport.nativeElement.clientHeight + 40;
    this.errorHandler.startLoader('Téléchargement en cours');
    domToImage
      .toPng(this.dataToExport.nativeElement, {
        width: width,
        height: height,
      })
      .then((result) => {
        let jsPdfOptions = {
          format: [width + 50, height + 220],
        };

        const pdf = new jsPDF(jsPdfOptions);
        pdf.setFontSize(48);
        pdf.setTextColor('#2585fe');

        const pdfTitle = `Programmation des examen \n`;
        pdf.text(pdfTitle, 25, 75);

        pdf.setFontSize(24);
        pdf.setTextColor('#131523');

        pdf.addImage(result, 'JPEG', 25, 85, width, height);
        pdf.save('programmation' + '.pdf');
        this.errorHandler.stopLoader();
      })
      .catch((error) => {
        this.errorHandler.stopLoader();
        // Gestion des erreurs (à compléter si nécessaire)
      });
  }

  sendNotifications() {
    this.onSending = true;
    this.composition
      .sendConvocationConduite({
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
        emitAlertEvent(response.message, 'success', 'bottom-right', true);
        this.onSending = false;
        this.generationStarted = false;
        this.errorHandler.stopLoader();
      });
  }

  private _getAnnexe(call: CallableFunction) {
    if (this.annexeId) {
      this.annexeService
        .findById(this.annexeId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.currentAnnexe = response.data;
          call();
        });
    }
  }

  onAnnexe() {
    this.currentSession.generated = false;
    this.showResult = false;
    this.candidatsApts = [];
    if (this.annexeId) {
      this.errorHandler.startLoader('Chargement en cours ...');
      this.fresh();
      //Lorsque l'annexe est trouvé on récupère les programmations
      this._getAnnexe(() => {
        this.getExamens(() => {
          this.errorHandler.startLoader('Récupération  de la programmation');
          this.getProgrammations();
        });
      });
    }
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
