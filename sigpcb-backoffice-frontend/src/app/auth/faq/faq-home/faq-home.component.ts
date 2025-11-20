import { Component, OnInit } from '@angular/core';
import { Faq } from 'src/app/core/interfaces/faq';
import { FaqService } from 'src/app/core/services/faq.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent, toFormData } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-faq-home',
  templateUrl: './faq-home.component.html',
  styleUrls: ['./faq-home.component.scss'],
})
export class FaqHomeComponent implements OnInit {
  currentFaqId: number | null = null;
  faq: Faq = { type: 'autoecole' } as Faq;
  faqLeft: Faq[] = [];
  faqRight: Faq[] = [];
  pageNumber = 1;
  paginate_data: any = {};
  filter = {
    type: null,
    page: this.pageNumber,
  };
  constructor(
    private faqService: FaqService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  faqs: Faq[] = [];
  ngOnInit(): void {
    this.get();
  }

  onFaqueSelected(faqId: number) {
    if (this.currentFaqId === faqId) {
      this.currentFaqId = null;
    } else {
      this.currentFaqId = faqId;
    }
  }

  openModal(faq?: Faq) {
    if (faq) {
      this.faq = faq;
    }
    $('#faq-modal').modal('show');
  }

  save() {
    this.faqService
      .post(this.faq)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.faq = { type: 'autoecole' } as Faq;
        this.get();
        emitAlertEvent('FAQ enregistrée avec succès', 'success');
      });
  }

  destroy(faq: Faq) {
    this.errorHandler.startLoader();
    this.faqService
      .delete(faq)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.errorHandler.stopLoader();
        emitAlertEvent('Faq supprimée avec succès', 'success');
      });
  }
  get() {
    this.errorHandler.startLoader('Chargement en cours ...');
    const filter: any = { ...this.filter };
    if (filter.type != 'autoecole' && filter.type != 'candidat') {
      delete filter.type;
    }
    this.faqService
      .get([filter])
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.faqs = this.paginate_data.data || [];
        const faqCount = this.faqs.length;
        const halfCount = Math.ceil(faqCount / 2);

        this.faqLeft = this.faqs.slice(0, halfCount);
        this.faqRight = this.faqs.slice(halfCount);
        this.errorHandler.stopLoader();
      });
  }

  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.pageNumber,
      totalItems: this.paginate_data?.total ?? 0,
    };
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.filter.page = this.pageNumber;
    this.get();
  }
}
