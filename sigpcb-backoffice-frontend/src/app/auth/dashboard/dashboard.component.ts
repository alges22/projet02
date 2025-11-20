import { Component, OnInit } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { Agenda } from 'src/app/core/interfaces/examens';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { DateService } from 'src/app/core/services/date.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent implements OnInit {
  annexes: AnnexeAnatt[] = [];
  agenda: any = {};
  examen: Agenda | null = null;
  constructor(
    private readonly breadcrumb: BreadcrumbService,
    private readonly dateService: DateService,
    private readonly examenService: ExamenService,
    private readonly annexeAnattService: AnnexeAnattService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this._setBreadcrum();
    this._getExamenRecent();
    //this.getAnnexeAnatt();
  }

  private _setBreadcrum() {
    this.breadcrumb.setBreadcrumbs('Tableau de bord', [
      {
        label: 'Accueil',
        route: '#',
      },
      {
        label: 'Tableau de bord',
        active: true,
      },
    ]);
  }

  private mapExamen(agenda: Agenda) {
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
    this.errorHandler.startLoader('Chargement de la session courante');
    this.examenService
      .recentExamen()
      .pipe(
        this.errorHandler.handleServerErrors(() => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        const recent = response.data;
        if (recent) {
          this.examen = recent;
          this.agenda = this.mapExamen(recent);
        }
        this.errorHandler.stopLoader();
      });
  }
}
