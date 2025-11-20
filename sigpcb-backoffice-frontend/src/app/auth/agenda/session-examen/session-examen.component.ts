import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { Agenda } from 'src/app/core/interfaces/examens';
import { Component, OnInit } from '@angular/core';
import { AgendaService } from 'src/app/core/services/agenda.service';
import { PdfService } from 'src/app/core/services/pdf.service';
import { DateService } from 'src/app/core/services/date.service';

@Component({
  selector: 'app-session-examen',
  templateUrl: './session-examen.component.html',
  styleUrls: ['./session-examen.component.scss'],
})
export class SessionExamenComponent implements OnInit {
  agendas: any[] = [];
  onLoadingAgenda = true;
  year = null as number | null;
  filters: any = {};
  months: any[] = [];
  constructor(
    private agendaService: AgendaService,
    private errorhandler: HttpErrorHandlerService,
    private pdfService: PdfService,
    private dateService: DateService
  ) {}
  ngOnInit(): void {
    const date = new Date();
    const anneeCourante = date.getFullYear();
    this.year = anneeCourante;
    this._getAgenda();
    this.months = this.dateService.fullMonths;
  }

  selectYear(year: number | null) {
    this.year = year;
    this._getAgenda();
  }
  private _getAgenda() {
    this.onLoadingAgenda = true;
    this.errorhandler.startLoader();
    this.agendaService
      .all(this.year, this.filters)
      .pipe(
        this.errorhandler.handleServerErrors((response) => {
          this.onLoadingAgenda = false;
        })
      )
      .subscribe((response) => {
        let agendas = response.data as Agenda[];

        this.agendas = agendas.map(this.mapProgramation.bind(this));
        this.onLoadingAgenda = false;
        this.errorhandler.stopLoader();
      });
  }

  search() {
    this._getAgenda();
  }

  private mapProgramation(agenda: any) {
    return {
      month: agenda.session,
      session_long: agenda.session_long,
      programation: agenda.programs || [],
      id: agenda.id,
      annexes: agenda.annexes ?? [],
    };
  }

  sortMonthsAndDays(a: any, b: any) {
    // Convertir les noms de mois en chiffres pour faciliter la comparaison
    const months: Record<string, number> = {
      janvier: 0,
      février: 1,
      mars: 2,
      avril: 3,
      mai: 4,
      juin: 5,
      juillet: 6,
      août: 7,
      septembre: 8,
      octobre: 9,
      novembre: 10,
      décembre: 11,
    };

    const monthA = months[a.month.toLowerCase()];
    const monthB = months[b.month.toLowerCase()];

    // Comparer les mois
    if (monthA < monthB) {
      return -1;
    }
    if (monthA > monthB) {
      return 1;
    }

    // Si les mois sont égaux, comparer les jours
    const dayA = a.programation[0].days;
    const dayB = b.programation[0].days;

    return dayA - dayB;
  }

  export() {
    const params: Record<string, string | number> = {};

    if (this.year) {
      params['year'] = this.year;
    }
    if (this.agendas.length) {
      this.errorhandler.startLoader('Téléchargement en cours');
      this.pdfService
        .agendas(params)
        .pipe(this.errorhandler.handleServerErrors())
        .subscribe((response) => {
          this.errorhandler.stopLoader();
          window.open(response.data, '_blank');
        });
    }
  }
}
