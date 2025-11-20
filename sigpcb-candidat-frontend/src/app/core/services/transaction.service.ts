import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { apiUrl } from 'src/app/helpers/helpers';
type TransactionResponse = {
  uuid: string;
  amount: number;
  date_payment: string;
  status: string;
  url: string;
};
@Injectable({
  providedIn: 'root',
})
export class TransactionService {
  timer: any = null;
  constructor(private http: HttpClient) {}

  paymentStatus(
    uuid: string,
    success: (data: TransactionResponse) => void,
    error: () => any
  ) {
    this.timer = setInterval(() => {
      this.http
        .get(apiUrl(`/eservices/get-transaction/${uuid}`))
        .pipe(error())
        .subscribe((res: any) => {
          success(res.data);
        });
    }, 5000);
  }

  stopTimer() {
    clearInterval(this.timer);
  }
}
