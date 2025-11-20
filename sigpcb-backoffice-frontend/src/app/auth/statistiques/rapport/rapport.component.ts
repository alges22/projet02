import { Agenda } from 'src/app/core/interfaces/examens';
import { Component, OnInit } from '@angular/core';
import {
  StatRapport,
  StatRapportCode,
} from 'src/app/core/interfaces/statistiques';
import { AgendaService } from 'src/app/core/services/agenda.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StatisticService } from 'src/app/core/services/statistic.service';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { PdfService } from 'src/app/core/services/pdf.service';
import { ExportType } from 'src/app/core/interfaces';

@Component({
  selector: 'app-rapport',
  templateUrl: './rapport.component.html',
  styleUrls: ['./rapport.component.scss'],
})
export class RapportComponent implements OnInit {
  stat: { langues: any[]; data: StatRapport[]; total: number } | null = null;
  rptype = 'codes' as 'codes' | 'conduites';
  rapportType = 'synthetique';
  rpActivities: {
    codes: {
      total: number;
      list: any[];
      percent: number;
      total_presents: number;
      total_abscent: number;
      total_echoues: number;
      total_admis: number;
      presence: number;
    };
    conduites: {
      total: number;
      list: any[];
      percent: number;
      total_presents: number;
      total_abscent: number;
      total_echoues: number;
      total_admis: number;
      presence: number;
    };
    total: number;
  } | null = null;
  agendas: Agenda[] = [];
  filters = {
    examen_id: null as null | number,
    annexe_id: null as null | number,
  };

  session: Agenda | null = null;
  year = 2023 as number | null;
  annexe: AnnexeAnatt | null = null;
  rapport = 'Rapport syntéthique' as
    | 'Rapport syntéthique'
    | "Rapport d'activité";
  annexes: AnnexeAnatt[] = [];

  stat_synts: {
    codes: StatRapportCode[];
  } | null = null;
  constructor(
    private statisticService: StatisticService,
    private agendaService: AgendaService,
    private errorhandler: HttpErrorHandlerService,
    private annexeAnattService: AnnexeAnattService,
    private pdfService: PdfService
  ) {}

  ngOnInit(): void {
    const date = new Date();
    const anneeCourante = date.getFullYear();
    this.year = anneeCourante;
    this._getAgendas();
    this._getAnnexes();
    this.fetch();
  }

  selectSession(target: any) {
    const sessionId = target.value;
    this.session = this.agendas.find((a) => a.id == sessionId) || null;
    if (!!this.session) {
      this.filters.examen_id = this.session.id;
    } else {
      this.filters.examen_id = null;
    }

    this.fetch();
  }

  selectYear(year: number | null) {
    this.year = year;
    this.filters.examen_id = null;
    this.session = null;

    this._getAgendas();
  }
  selectAnnexe(target: any) {
    const annexeId = target.value;
    this.annexe = this.annexes.find((a) => a.id == annexeId) || null;
    if (!!this.annexe) {
      this.filters.annexe_id = this.annexe.id;
    } else {
      this.filters.annexe_id = null;
    }

    this.fetch(this.rapportType);
  }
  private _getAgendas() {
    this.errorhandler.startLoader();
    this.agendaService
      .all(this.year)
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        this.agendas = response.data;
        this.errorhandler.stopLoader();
      });
  }

  private _getAnnexes() {
    this.annexeAnattService
      .get()
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        this.annexes = response.data;
      });
  }

  fetch(type = 'synthetique') {
    const param: Record<string, any> = {};
    if (this.filters.examen_id) {
      param['examen_id'] = this.filters.examen_id;
    }

    if (this.filters.annexe_id) {
      param['annexe_id'] = this.filters.annexe_id;
    }
    param['perYear'] = this.year;

    param['type'] = type;
    this.rapportType = type;
    this.errorhandler.startLoader();
    this.statisticService
      .get(param)
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        if (type == 'synthetique') {
          this.stat = response.data;
        } else {
          this.rpActivities = response.data;
        }
        this.errorhandler.stopLoader();
      });
  }

  downloadAsPdf(type: ExportType = 'pdf'): void {
    const params: Record<string, string | number> = {};

    let slug = 'rapport-statistics';
    if (type != 'pdf') {
      slug = 'statistiques-excel';
    }
    if (this.annexe) {
      params['annexe_id'] = this.annexe.id;
    }
    params['format'] = type;

    if (this.session) {
      params['examen_id'] = this.session.id;

      this.errorhandler.startLoader('Téléchargement en cours');
      this.pdfService
        .rapportStatistique(params, slug)
        .pipe(this.errorhandler.handleServerErrors())
        .subscribe((response) => {
          this.errorhandler.stopLoader();
          window.open(response.data, '_blank');
        });
    }
  }

  selectRapport(rapport: 'Rapport syntéthique' | "Rapport d'activité") {
    this.rapport = rapport;
    if (this.rapport == "Rapport d'activité") {
      if (!this.rpActivities) {
        this.fetch('activity');
      }
    } else {
      if (!this.stat) {
        this.fetch();
      }
    }
  }
}
