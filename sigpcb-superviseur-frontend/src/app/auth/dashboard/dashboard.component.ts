import { Component, OnInit } from '@angular/core';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent implements OnInit {
  constructor(
    private readonly examenService: ExamenService,
    private readonly errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.examenService
      .sessionEnCours()
      .pipe(this.errorHandler.handleServerErrors((response) => {}))
      .subscribe((response) => {
        this.examenService.setupCurrentSession(response.data);
      });
  }
}
