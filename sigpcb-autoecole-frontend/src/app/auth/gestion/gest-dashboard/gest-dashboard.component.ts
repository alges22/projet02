import { Component, OnInit } from '@angular/core';
import { Agenda } from 'src/app/core/interfaces/date';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { DateService } from 'src/app/core/services/date.service';
import { ExamenService } from 'src/app/core/services/examen.service';

@Component({
  selector: 'app-gest-dashboard',
  templateUrl: './gest-dashboard.component.html',
  styleUrls: ['./gest-dashboard.component.scss'],
})
export class GestDashboardComponent implements OnInit {
  programmation: any = {};
  constructor(
    private breadcrumb: BreadcrumbService,
    private examenService: ExamenService,
    private dateService: DateService
  ) {}

  ngOnInit(): void {
    this.breadcrumb.setBreadcrumbs('Tableau de board', [
      {
        label: 'Accueil',
        route: '/gestions/home',
      },
      {
        label: 'Tableau de board',
        route: '',
        active: true,
      },
    ]);
    this._getExamenRecent();
  }

  private mapProgramation(agenda: Agenda) {
    const open_d = new Date(agenda.debut_etude_dossier_at);
    const openDay = {
      days: open_d.getUTCDate(),
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

  private _getExamenRecent() {
    const data = {
      month: new Date().getUTCMonth() + 1,
      year: new Date().getFullYear(),
    };

    this.examenService.getExemens([data]).subscribe((response) => {
      const recent = response.data[0];
      if (recent) {
        if (recent.id) {
          this.programmation = this.mapProgramation(recent);
        }
      }
    });
  }
}
