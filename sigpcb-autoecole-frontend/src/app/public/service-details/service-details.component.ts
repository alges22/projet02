import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { PrestationService } from 'src/app/core/prestation/prestation.service';

@Component({
  selector: 'app-service-details',
  templateUrl: './service-details.component.html',
  styleUrls: ['./service-details.component.scss'],
})
export class ServiceDetailsComponent implements OnInit {
  serviceSlug = '';

  constructor(
    private route: ActivatedRoute,
    private prestation: PrestationService
  ) {}

  ngOnInit(): void {
    this.getServiceSlugFromRoute();
  }

  getServiceSlugFromRoute(): void {
    this.route.paramMap.subscribe((params) => {
      this.serviceSlug = params.get('slug') || '';
    });
  }
}
