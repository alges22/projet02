import { Component } from '@angular/core';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';

@Component({
  selector: 'app-service-client',
  templateUrl: './service-client.component.html',
  styleUrls: ['./service-client.component.scss'],
})
export class ServiceClientComponent {
  constructor(private breadcrumb: BreadcrumbService) {}
  ngOnInit(): void {
    this._setBreadcrumbs();
  }
  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs('Service Client', [
      {
        label: 'Tableau de bord',
        route: '/gestions/home',
      },
      {
        label: 'Service Client',
        active: true,
      },
    ]);
  }
}
