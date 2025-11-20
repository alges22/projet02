import { Component, Input, OnInit, OnDestroy } from '@angular/core';

@Component({
  selector: 'app-date-counter',
  templateUrl: './date-counter.component.html',
  styleUrls: ['./date-counter.component.scss'],
})
export class DateCounterComponent implements OnInit, OnDestroy {
  @Input() date: string | null = null;
  joursRestants: number = 0;
  private intervalId: any;

  ngOnInit(): void {
    this.start();
    // Mettre à jour le compteur toutes les 24 heures
    // this.intervalId = setInterval(() => {
    //   this.start();
    // }, 24 * 60 * 60 * 1000);
  }

  ngOnDestroy(): void {
    // Nettoyer l'intervalle lorsque le composant est détruit
    clearInterval(this.intervalId);
  }

  private start() {
    if (this.date) {
      const dateExpiration = new Date(this.date);
      const dateActuelle = new Date();
      const diffTemps = dateExpiration.getTime() - dateActuelle.getTime();
      const diffJours = Math.ceil(diffTemps / (1000 * 3600 * 24)); // Convertit la différence en jours arrondis
      this.joursRestants = diffJours;
    }
  }
}
