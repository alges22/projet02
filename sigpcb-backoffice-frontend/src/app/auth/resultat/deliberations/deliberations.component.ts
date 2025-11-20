import { Component } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { Agenda } from 'src/app/core/interfaces/examens';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-deliberations',
  templateUrl: './deliberations.component.html',
  styleUrls: ['./deliberations.component.scss'],
})
export class DeliberationsComponent {
  annexes: AnnexeAnatt[] = [];
  agendas: Agenda[] = [];
  tabs: NavTab[] = [];
  counts: Record<string, any> = {};
  constructor(
    private breadcrumb: BreadcrumbService,
    private annexeAnattService: AnnexeAnattService,
    private errorHandler: HttpErrorHandlerService,
    private examenService: ExamenService,
    private navigation: NavigationService
  ) {}
  ngOnInit(): void {
    this._setBreadcrumbs();
    this.getAnnexeAnatt();
    this.getExamens();

    this._setTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Résultats d'examens`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Délibérations`,
        active: true,
      },
    ]);
  }

  private getAnnexeAnatt() {
    this.annexeAnattService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.annexes = response.data;
      });
  }

  private getExamens() {
    this.errorHandler.startLoader('Récupération des sessions ...');
    this.examenService
      .getExemens()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.agendas = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
