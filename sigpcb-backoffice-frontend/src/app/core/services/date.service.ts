import { Injectable } from '@angular/core';
import { FullDay, FullMonth } from '../interfaces/date';

@Injectable({
  providedIn: 'root',
})
export class DateService {
  fullMonths: FullMonth[] = [
    'Janvier',
    'Février',
    'Mars',
    'Avril',
    'Mai',
    'Juin',
    'Juillet',
    'Août',
    'Septembre',
    'Octobre',
    'Novembre',
    'Décembre',
  ];

  fullDays: FullDay[] = [
    'Lundi',
    'Mardi',
    'Mercredi',
    'Jeudi',
    'Vendredi',
    'Samedi',
    'Dimanche',
  ];

  shortMonths = [
    'Jan', // Janvier
    'Fév', // Février
    'Mar', // Mars
    'Avr', // Avril
    'Mai', // Mai
    'Juin', // Juin
    'Juil', // Juillet
    'Août', // Août
    'Sep', // Septembre
    'Oct', // Octobre
    'Nov', // Novembre
    'Déc', // Décembre
  ];

  constructor() {}

  getFullMonths(): FullMonth[] {
    return this.fullMonths;
  }

  getFullDays(): FullDay[] {
    return this.fullDays;
  }

  getMonthFrom(start_month: FullMonth): FullMonth[] {
    const startIndex = this.fullMonths.indexOf(start_month);
    if (startIndex === -1) {
      throw new Error("Le mois de départ n'existe pas dans la liste des mois.");
    }
    return this.fullMonths.slice(startIndex);
  }

  getDayFrom(start_day: FullDay): FullDay[] {
    const startIndex = this.fullDays.indexOf(start_day);
    if (startIndex === -1) {
      throw new Error(
        "Le jour de départ n'existe pas dans la liste des jours."
      );
    }

    return this.fullDays.slice(startIndex);
  }

  getFullMonthNow(): FullMonth {
    return this.getFullMonthFromDay(new Date().getMonth());
  }

  getFullMonthFromDay(index: number): FullMonth {
    return this.fullMonths[index] || 'Janvier';
  }

  getHumanDay(index: number): string {
    return this.fullDays[index];
  }
  findInShortMonth(month_number: number): string {
    return this.shortMonths[month_number];
  }

  findOrCorrect(month: string) {
    return this.fullDays.find((m) => m == month) || null;
  }
  getMonthFromDate(date: Date): FullMonth {
    const monthIndex = date.getMonth();
    return this.getFullMonthFromDay(monthIndex);
  }
}
