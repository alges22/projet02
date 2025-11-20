import { Faq } from 'src/app/core/interfaces/faq';
import { BreadcrumbService } from './../../../core/services/breadcrumb.service';
import { Component, OnInit } from '@angular/core';
import { FaqService } from 'src/app/core/services/faq.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-faq',
  templateUrl: './faq.component.html',
  styleUrls: ['./faq.component.scss'],
})
export class FaqComponent implements OnInit {
  currentFaqId: number | null = null;
  faqLeft: Faq[] = [];
  faqRight: Faq[] = [];

  faqs: Faq[] = [];

  constructor(
    private breadcrumb: BreadcrumbService,
    private faqService: FaqService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this._setBreadcrumbs();
    this.get();
  }

  onFaqueSelected(faqId: number) {
    if (this.currentFaqId === faqId) {
      this.currentFaqId = null;
    } else {
      this.currentFaqId = faqId;
    }
  }
  get() {
    this.errorHandler.startLoader('Chargement en cours ...');
    this.faqService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.faqs = response.data;
        this.divideFaq();
        this.errorHandler.stopLoader();
      });
  }

  divideFaq() {
    const faqCount = this.faqs.length;
    const halfCount = Math.ceil(faqCount / 2);

    this.faqLeft = this.faqs.slice(0, halfCount);
    this.faqRight = this.faqs.slice(halfCount);
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs('Questions fréquentes', [
      {
        label: 'Tableau de bord',
        route: '/gestions/home',
      },
      {
        label: 'Questions fréquentes',
        active: true,
      },
    ]);
  }
}
