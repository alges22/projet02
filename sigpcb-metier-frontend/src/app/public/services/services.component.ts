import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { PrestationService } from 'src/app/core/prestation/prestation.service';

@Component({
  selector: 'app-services',
  templateUrl: './services.component.html',
  styleUrls: ['./services.component.scss'],
})
export class ServicesComponent {
  serviceSlug = '';
  rejetId: string | null = null;
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
      this.rejetId = params.get('rejetId');
    });
  }
}
