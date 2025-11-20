import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { StorageService } from 'src/app/core/services/storage.service';

@Component({
  selector: 'app-logout',
  templateUrl: './logout.component.html',
  styleUrls: ['./logout.component.scss'],
})
export class LogoutComponent implements OnInit {
  private timer: any;
  time = 10;
  constructor(private storage: StorageService, private router: Router) {}
  ngOnInit(): void {
    // this.pageService.changePage('logout');
    this.storage.destroy();
    localStorage.clear();
    this.startTimer();
  }
  startTimer() {
    this.timer = setInterval(() => {
      this.time--;
      if (this.time <= 0) {
        this.stopTimer();
        window.location.href = '';
      }
    }, 1000);
  }

  stopTimer() {
    clearInterval(this.timer);
  }
  ngOnDestroy(): void {
    this.stopTimer();
    if (document.fullscreenElement) {
      document.exitFullscreen().catch((err) => {
        console.error(
          `Erreur lors de la tentative de sortie du mode plein écran: ${err.message}`
        );
      });
    }
  }
}
