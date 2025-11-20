import { Component, Input } from '@angular/core';
type CalendProgress = {
  days: number | string;
  month: string;
  label: string;
  color: string;
  end?: boolean; //Permetra de savoir s c'est la fin du program
  details?: string; //
};

@Component({
  selector: 'app-calendar-pogress',
  templateUrl: './calendar-pogress.component.html',
  styleUrls: ['./calendar-pogress.component.scss'],
})
export class CalendarPogressComponent {
  @Input() programation: CalendProgress[] = [];
}
