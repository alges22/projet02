import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CompositionService } from 'src/app/core/services/composition.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-statistiques',
  templateUrl: './statistiques.component.html',
  styleUrls: ['./statistiques.component.scss'],
})
export class StatistiquesComponent {
  // annexeAnatts: AnnexeAnatt[] = [];
  counts: Record<string, number> = {};
  tabs: NavTab[] = [];
  constructor(
    private breadcrumb: BreadcrumbService,
    private navigation: NavigationService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    // this.getAnnexeAnatt();

    this._setTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Statistitques`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Statistiques`,
        active: true,
      },
    ]);
  }

  // private getAnnexeAnatt() {
  //   this.annexeAnattService
  //     .get()
  //     .pipe(this.errorHandler.handleServerErrors())
  //     .subscribe((response) => {
  //       this.annexeAnatts = response.data;
  //     });
  // }

  // annexeSelected(event: any): void {
  //   const path = this.location.path();
  //   if (event.target.value != 0) {
  //     this.composition.setAnnexeCompo(event.target.value);
  //   } else {
  //     this.composition.setAnnexeCompo(null);
  //   }

  //   this.router.navigateByUrl(path);
  // }
  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
