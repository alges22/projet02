import {
  Component,
  Input,
  AfterViewInit,
  Output,
  EventEmitter,
  OnDestroy,
} from '@angular/core';
import { environment } from 'src/environments/environment';
import { TransactionService } from '../../services/transaction.service';
import { HttpErrorHandlerService } from '../../services/http-error-handler.service';

@Component({
  selector: 'app-fedapay-box',
  templateUrl: './fedapay-box.component.html',
  styleUrls: ['./fedapay-box.component.scss'],
})
export class FedapayBoxComponent implements AfterViewInit, OnDestroy {
  @Input() transaction: { id: string | number; uuid: string } | null = null;
  @Output() onCompleted = new EventEmitter<any>();
  constructor(
    private transactionService: TransactionService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit() {}

  ngAfterViewInit(): void {
    const fedapay = window.FedaPay;

    //Lancement du modale
    const fedapayOptions = fedapay.init({
      public_key: environment.fedapay.key,
      environment: environment.fedapay.sandbox,
      transaction: {
        id: this.transaction?.id,
      },
    });

    fedapayOptions.open();
    // Attends 5s d'abord, avant lancer la vérification
    setTimeout(() => {
      this.checkPaymentStatus(this.transaction?.uuid ?? '', () => {
        //Dès le que le paiement est vérifié, on retire le modal de paiement fedapay
        const modal = document.getElementById(fedapayOptions.modalId);

        if (modal) {
          modal.remove();
        }
      });
    }, 5000);
  }

  /**
   *
   * @param uuid
   * @param completed
   */
  private checkPaymentStatus(uuid: string, completed: () => void) {
    //Vérification
    this.transactionService.paymentStatus(
      uuid,
      (data) => {
        if (data.status !== 'init') {
          this.transactionService.stopTimer();
          completed();
          this.onCompleted.emit(data);
        }

        console.log(data);
      },
      () => this.errorHandler.handleServerErrors()
    );
  }
  ngOnDestroy(): void {
    this.transactionService.stopTimer();
  }
}
