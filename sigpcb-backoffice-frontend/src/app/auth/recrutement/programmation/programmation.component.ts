import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { NavigationService } from 'src/app/core/services/navigation.service';
import { Location } from '@angular/common';
import { CompoRecrutementService } from 'src/app/core/services/compo-recrutement.service';

@Component({
  selector: 'app-programmation',
  templateUrl: './programmation.component.html',
  styleUrls: ['./programmation.component.scss'],
})
export class ProgrammationComponent {
  annexeAnatts: AnnexeAnatt[] = [];
  counts: Record<string, number> = {};
  tabs: NavTab[] = [];
  constructor(
    private breadcrumb: BreadcrumbService,
    private composition: CompoRecrutementService,
    private annexeAnattService: AnnexeAnattService,
    private errorHandler: HttpErrorHandlerService,
    private router: Router,
    private location: Location,
    private navigation: NavigationService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    this.getAnnexeAnatt();

    this._setTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Programmation des compositions`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Programmation des compositions`,
        active: true,
      },
    ]);
  }

  private getAnnexeAnatt() {
    this.annexeAnattService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.annexeAnatts = response.data;
      });
  }

  annexeSelected(event: any): void {
    const path = this.location.path();
    if (event.target.value != 0) {
      this.composition.setAnnexeCompo(event.target.value);
    } else {
      this.composition.setAnnexeCompo(null);
    }

    this.router.navigateByUrl(path);
  }
  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
