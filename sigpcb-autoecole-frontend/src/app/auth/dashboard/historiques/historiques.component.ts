import { Component } from '@angular/core';
import { Promoteur } from 'src/app/core/interfaces/user.interface';
import { HistoriqueService } from 'src/app/core/services/historique.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';

@Component({
  selector: 'app-historiques',
  templateUrl: './historiques.component.html',
  styleUrls: ['./historiques.component.scss'],
})
export class HistoriquesComponent {
  auth: Promoteur | null = null;
  historiques: any[] = [];
  ready = false;
  constructor(
    private historiqService: HistoriqueService,
    private storage: StorageService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.auth = this.storage.get('auth');
    this._getHistoriques();
  }

  private _getHistoriques() {
    this.errorHandler.startLoader();
    this.historiqService
      .get()
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.ready = true;
        })
      )
      .subscribe((response) => {
        this.historiques = response.data;
        this.ready = true;
        this.errorHandler.stopLoader();
      });
  }

  fresh() {
    this._getHistoriques();
  }
}
