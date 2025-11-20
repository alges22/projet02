import { Component, OnInit } from '@angular/core';
import { AlertType } from '../../interfaces/alert';
import { AlertService } from '../../services/alert.service';

@Component({
  selector: 'app-alert',
  templateUrl: './alert.component.html',
  styleUrls: ['./alert.component.scss'],
})
export class AlertComponent implements OnInit {
  message = "Une erreur s'est produite";
  type: AlertType = 'danger';
  hasAlert = false;
  callback?: CallableFunction;
  constructor(private alertService: AlertService) {}

  ngOnInit(): void {
    this.alertService.onAlert().subscribe((alert) => {
      this.hasAlert = !!alert;

      if (alert) {
        this.message = alert.message;
        this.type = alert.type;
        this.callback = alert.call;
      }
    });
  }

  close() {
    if (this.callback) {
      this.callback();
    }
    this.message = '';
    this.hasAlert = false;
  }
}
