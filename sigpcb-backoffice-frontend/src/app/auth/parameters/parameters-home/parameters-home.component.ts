import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-parameters-home',
  templateUrl: './parameters-home.component.html',
  styleUrls: ['./parameters-home.component.scss'],
})
export class ParametersHomeComponent implements OnInit {
  constructor(
    private http: Router,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.errorHandler.startLoader('Chargement en cours');
    this.http.navigate(['/parametres/base/langues']);
  }
}
