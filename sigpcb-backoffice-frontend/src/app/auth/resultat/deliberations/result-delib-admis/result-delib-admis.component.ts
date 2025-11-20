import { Component, OnInit } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Agenda } from 'src/app/core/interfaces/examens';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PdfService } from 'src/app/core/services/pdf.service';
import { ResultatService } from 'src/app/core/services/resultat.service';
type ResultatValue =
  | 'all'
  | 'admis'
  | 'recales'
  | 'admis-code'
  | 'recales-code'
  | 'recales-conduite'
  | 'absents-code'
  | 'absents-conduite';
@Component({
  selector: 'app-result-delib-admis',
  templateUrl: './result-delib-admis.component.html',
  styleUrls: ['./result-delib-admis.component.scss'],
})
export class ResultDelibAdmisComponent implements OnInit {
  /**
   * Les paramètres de filtrage
   */
  filters = {
    categorie_permis_id: null as number | null,
    annexe_id: null as number | null,
    examen_id: null as number | null,
    search: null as number | null,
    resultat_code: null as string | null,
    resultat_conduite: null as string | null,
    presence: null as 'abscent' | null | 'present',
    presence_conduite: null as 'present' | null | 'absent',
  };
  openedIndex: number | null = null;
  resultats: any[] | -1 = [];
  categories: CategoryPermis[] = []; //
  examen: Agenda | null = null;
  resultatsReady = false;
  showValidateAlert = false;
  annexe: AnnexeAnatt | null = null;
  list = 'all' as ResultatValue;
  constructor(
    private categoryPermisService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService,
    private annexeService: AnnexeAnattService,
    private examenService: ExamenService,
    private resultatService: ResultatService,
    private pdfService: PdfService
  ) {}

  ngOnInit(): void {
    this.getCategories();
    this._annexeChanged();
    this._sessionChanged();
  }
  /**
   * Obtenir les catégories de permis.
   */
  getCategories() {
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
      });
  }

  filter(target: any) {
    const value = target.value as ResultatValue;
    this.filters.resultat_conduite = null;
    this.filters.resultat_code = null;
    this.filters.presence = null;
    this.filters.presence_conduite = null;
    if (value == 'admis') {
      this.filters.resultat_conduite = 'success';
    } else if (value == 'admis-code') {
      this.filters.resultat_code = 'success';
    } else if (value == 'recales') {
      this.filters.resultat_conduite = 'failed';
    } else if (value == 'recales-code') {
      this.filters.resultat_code = 'failed';
    } else if (value == 'recales-conduite') {
      this.filters.resultat_code = 'success';
      this.filters.resultat_conduite = 'failed';
    } else if (value == 'absents-code') {
      this.filters.presence = 'abscent';
    } else if (value == 'absents-conduite') {
      this.filters.presence_conduite = 'absent';
    }
    this.getResultats();
  }

  open(i: number) {
    if (this.openedIndex == i) {
      this.openedIndex = null;
    } else {
      this.openedIndex = i;
    }
  }
  private _resultats() {
    if (this.annexe && this.examen) {
      this.errorHandler.startLoader('Chargement du résultat...');
      this.resultatsReady = false;
      // this.codeReady = false;
      this.resultatService
        .deliberations({ result: this.list, ...this.filters })
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.resultatsReady = true;
          })
        )
        .subscribe((response) => {
          this.resultatsReady = true;
          this.resultats = response.data;

          this.errorHandler.stopLoader();
        });
    }
  }

  private _annexeChanged() {
    this.annexeService.onAnnexeChange().subscribe((annexe) => {
      if (annexe !== null) {
        this.annexe = annexe;
        this.filters.annexe_id = annexe.id;
        this._resultats();
      }
    });
  }

  private _sessionChanged() {
    this.examenService.currentSession().subscribe((session) => {
      //Si une session est en cours
      if (!!session) {
        this.examen = session;
        this.filters.examen_id = session.id;
      }
      this._resultats();
    });
  }
  getResultats() {
    this._resultats();
  }

  downloadAsPdf(): void {
    this.errorHandler.startLoader('Téléchargement en cours');
    this.pdfService
      .download('resultat-examen', { result: this.list, ...this.filters })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        window.open(response.data, '_blank');
      });
  }

  getResultatLabel(ds: any) {
    if (this.list === 'admis-code' || this.list === 'admis') {
      return 'Admis(e)';
    } else if (this.list === 'recales-code' || this.list === 'recales') {
      return 'Recalé(e)';
    } else if (this.list === 'absents-code') {
      return 'Absent(e)';
    } else {
      if (ds.resultat_conduite) {
        return ds.resultat_conduite === 'success' ? 'Admis(e)' : 'Recalé(e)';
      }

      return '--';
    }
  }
}
