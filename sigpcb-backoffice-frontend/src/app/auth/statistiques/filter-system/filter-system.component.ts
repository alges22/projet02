import { Component } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Agenda } from 'src/app/core/interfaces/examens';
import { Langue } from 'src/app/core/interfaces/langue';
import { AgendaService } from 'src/app/core/services/agenda.service';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { PdfService } from 'src/app/core/services/pdf.service';
import { StatisticService } from 'src/app/core/services/statistic.service';
import { ChartType } from 'chart.js';
type StatData = {
  admis: number;
  echecs: number;
  admis_percent: number;
  echecs_percent: number;
};
@Component({
  selector: 'app-filter-system',
  templateUrl: './filter-system.component.html',
  styleUrls: ['./filter-system.component.scss'],
})
export class FilterSystemComponent {
  stat: {
    codes: StatData;
    conduites: StatData;
    total: number;
  } | null = null;
  agendas: Agenda[] = [];
  filters = {
    examen_id: 0 as null | number,
    annexe_id: 0 as null | number,
    categorie_permis_id: 0 as null | number,
    langue_id: 0 as null | number,
    sexe: null as null | 'M' | 'F',
    taux: 'admissibility' as 'admissibility' | 'inscription',
  };
  langues: Langue[] = [];
  permis: CategoryPermis | null = null;
  langue: Langue | null = null;
  session: Agenda | null = null;
  sexe: 'Hommes' | 'Femmes' | null = null;
  year = 2023 as number | null;
  annexes: AnnexeAnatt[] = [];
  annexe: AnnexeAnatt | null = null;
  categories: CategoryPermis[] = []; //
  public type: ChartType = 'pie';
  codeDatasets: any[] = [];
  conduitesDatasets: any[] = [];
  chartFilters = {
    type: 'line' as 'line' | 'bar' | 'pie',
    statfor: 'permis' as 'langue' | 'permis' | 'sexe' | 'annexe',
    factor: 'inscription' as 'admissibility' | 'inscription',
    examen_id: null as string | null | number,
    annexe_id: null as string | null | number,
  };

  chartData: any = null;
  types: { type: 'line' | 'bar' | 'pie'; name: string }[] = [
    {
      type: 'line',
      name: 'Graphe linéaire',
    },

    {
      type: 'bar',
      name: 'Graphe en batton',
    },
  ];
  options: any = {
    scales: {
      y: {
        ticks: {
          precision: 0, // Précision des étiquettes de l'axe des ordonnées
          beginAtZero: true, // Commencer l'axe à zéro
        },
      },
    },
  };
  constructor(
    private statisticService: StatisticService,
    private agendaService: AgendaService,
    private errorhandler: HttpErrorHandlerService,
    private annexeAnattService: AnnexeAnattService,
    private pdfService: PdfService,
    private categoryPermisService: CategoryPermisService,
    private langueService: LangueService
  ) {}

  ngOnInit(): void {
    const date = new Date();
    const anneeCourante = date.getFullYear();
    this.year = anneeCourante;
    this.fetch();
    this._getAgendas();
    this._getAnnexes();
    this._getCategories();
    this._getLangues();
  }

  private _getAnnexes() {
    this.annexeAnattService
      .get()
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        this.annexes = response.data;
      });
  }

  selectYear(year: number | null) {
    this.year = year;
    this.filters.examen_id = null;
    this.session = null;

    this._getAgendas();
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

  fetch() {
    const param: Record<string, any> = {};
    if (Number(this.filters.examen_id)) {
      param['examen_id'] = this.filters.examen_id;
      this.chartFilters.examen_id = this.filters.examen_id;
    } else {
      this.session = null;
    }

    if (['F', 'M'].includes(this.filters.sexe as any)) {
      param['sexe'] = this.filters.sexe;
      this.sexe = this.filters.sexe == 'M' ? 'Hommes' : 'Femmes';
    } else {
      this.sexe = null;
    }

    if (Number(this.filters.categorie_permis_id)) {
      param['categorie_permis_id'] = this.filters.categorie_permis_id;
      this.permis =
        this.categories.find((c) => c.id == this.filters.categorie_permis_id) ||
        null;
    } else {
      this.permis = null;
      this.filters.categorie_permis_id = 0;
    }

    if (Number(this.filters.langue_id)) {
      param['langue_id'] = this.filters.langue_id;
      this.langue =
        this.langues.find((langue) => langue.id == this.filters.langue_id) ||
        null;
    } else {
      this.langue = null;
      this.filters.langue_id = 0;
    }
    if (Number(this.filters.annexe_id)) {
      param['annexe_id'] = this.filters.annexe_id;
      this.chartFilters.annexe_id = this.filters.annexe_id;
    } else {
      this.annexe = null;
      this.chartFilters.annexe_id = 0;
    }
    this.chartFilters.examen_id = Number(this.chartFilters.examen_id);

    this.onChart();
    //A ne pas changer
    param['type'] = 'global';
    param['perYear'] = this.year;
    this.errorhandler.startLoader();
    this.statisticService
      .get(param)
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        this.stat = response.data;
        if (this.stat) {
          this.setCodeDatasets([
            this.stat.codes.admis_percent,
            this.stat.codes.echecs_percent,
          ]);

          this.setConduitesDatasets([
            this.stat.conduites.admis_percent,
            this.stat.conduites.echecs_percent,
          ]);
        }
        this.errorhandler.stopLoader();
      });
  }

  selectAnnexe(target: any) {
    const annexeId = target.value;
    this.annexe = this.annexes.find((a) => a.id == annexeId) || null;
    if (!!this.annexe) {
      this.filters.annexe_id = this.annexe.id;
    } else {
      this.filters.annexe_id = null;
    }

    this.fetch();
  }

  _getCategories() {
    this.categoryPermisService
      .all()
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
      });
  }

  _getLangues() {
    this.errorhandler.startLoader();
    this.langueService
      .all()
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.langues = response.data;
          this.errorhandler.stopLoader();
        }
      });
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

  private setCodeDatasets(numbers: number[]) {
    this.codeDatasets = [
      {
        label: "Taux d'admissibilité",
        data: numbers,
        backgroundColor: ['#006F6F', '#F49E24'],
        borderColor: ['#006F6F', '#F49E24'],
        borderWidth: 1,
      },
    ];
  }

  private setConduitesDatasets(numbers: number[]) {
    this.conduitesDatasets = [
      {
        label: "Taux d'admissibilité",
        data: numbers,
        backgroundColor: ['#006F6F', '#F49E24'],
        borderColor: ['#006F6F', '#F49E24'],
        borderWidth: 1,
      },
    ];
  }

  private _chooseChart() {
    this.errorhandler.startLoader();
    this.statisticService
      .charts('candidats', this.chartFilters)
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        this.chartData = response.data;
        this.errorhandler.stopLoader();
      });
  }

  onChart() {
    this._chooseChart();
  }
  onChartType(type: any) {
    this.chartFilters.type = type;
    this.onChart();
  }
}
