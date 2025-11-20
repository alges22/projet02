import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CompositionService } from 'src/app/core/services/composition.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { NavigationService } from 'src/app/core/services/navigation.service';
import { Location } from '@angular/common';

@Component({
  selector: 'app-reporting',
  templateUrl: './reporting.component.html',
  styleUrls: ['./reporting.component.scss'],
})
export class ReportingComponent {
  // annexeAnatts: AnnexeAnatt[] = [];
  counts: Record<string, number> = {};
  tabs: NavTab[] = [];
  constructor(
    private breadcrumb: BreadcrumbService,
    private composition: CompositionService,
    // private annexeAnattService: AnnexeAnattService,
    private errorHandler: HttpErrorHandlerService,
    private router: Router,
    private location: Location,
    private navigation: NavigationService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    // this.getAnnexeAnatt();

    this._setTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Reporting`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Reporting`,
        active: true,
      },
    ]);
  }

  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
