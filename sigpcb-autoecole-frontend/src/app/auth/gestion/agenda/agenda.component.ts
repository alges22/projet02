import { Component, OnInit } from '@angular/core';
import { Agenda } from 'src/app/core/interfaces/date';
import { AgendaService } from 'src/app/core/services/agenda.service';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { DateService } from 'src/app/core/services/date.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-agenda',
  templateUrl: './agenda.component.html',
  styleUrls: ['./agenda.component.scss'],
})
export class AgendaComponent implements OnInit {
  agendas: any = [];
  onLoadingAgenda = true;
  constructor(
    private agendaService: AgendaService,
    private dateService: DateService,
    private errorhandler: HttpErrorHandlerService,
    private breadcrumb: BreadcrumbService
  ) {}
  ngOnInit(): void {
    this._setBreadcrumbs();
    this._getAgenda();
  }

  private _getAgenda() {
    this.onLoadingAgenda = true;
    this.errorhandler.startLoader();
    this.agendaService
      .all()
      .pipe(
        this.errorhandler.handleServerErrors((response) => {
          this.onLoadingAgenda = false;
        })
      )
      .subscribe((response) => {
        let agendas = response.data as Agenda[];
        const pograms = agendas
          .map(this.mapProgramation.bind(this))
          .sort(this.sortMonthsAndDays.bind(this));
        this.agendas = pograms;
        this.onLoadingAgenda = false;
        this.errorhandler.stopLoader();
      });
  }

  private mapProgramation(agenda: Agenda) {
    const open_d = new Date(agenda.debut_etude_dossier_at);
    const openDay = {
      days: open_d.getUTCDay(),
      month: this.dateService.findInShortMonth(open_d.getUTCMonth()),
      color: '#00A884',
      label: 'Etude dossiers',
      end: false,
      id: agenda.id,
    };

    const gest_d = new Date(agenda.fin_etude_dossier_at);
    const gestDay = {
      days: gest_d.getUTCDate(),
      month: this.dateService.findInShortMonth(gest_d.getUTCMonth()),
      color: '#00A884',
      label: 'Gestion des rejets',
      end: false,
      id: agenda.id,
    };

    const conv_d = new Date(agenda.date_convocation);
    const convDay = {
      days: conv_d.getUTCDate(),
      month: this.dateService.findInShortMonth(conv_d.getUTCMonth()),
      color: 'orange',
      label: 'Convocation',
      end: false,
      id: agenda.id,
    };

    const code_d = new Date(agenda.date_code);
    const codeDay = {
      days: code_d.getUTCDate(),
      month: this.dateService.findInShortMonth(code_d.getUTCMonth()),
      color: '#0164BC',
      label: 'Composition',
      end: false,
      details: 'Composition (code)',
      id: agenda.id,
    };

    const conduite_d = new Date(agenda.date_conduite);
    const conduiteDay = {
      days: conduite_d.getUTCDate(),
      month: this.dateService.findInShortMonth(conduite_d.getUTCMonth()),
      color: '#0164BC',
      label: 'Composition',
      end: true,
      details: 'Composition (conduite)',
      id: agenda.id,
    };

    return {
      month: agenda.mois,
      programation: [openDay, gestDay, convDay, codeDay, conduiteDay],
      id: agenda.id,
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

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Agenda`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Agenda`,
        active: true,
      },
    ]);
  }
}
