import { Component, Input } from '@angular/core';
import { Agenda } from '../../interfaces/examens';
import { AnnexeAnatt } from '../../interfaces/annexe-anatt';
import { DateService } from '../../services/date.service';

@Component({
  selector: 'app-anatt-doc-conduite',
  templateUrl: './anatt-doc-conduite.component.html',
  styleUrls: ['./anatt-doc-conduite.component.scss'],
})
export class AnattDocConduiteComponent {
  @Input() programs: Record<string, any> = {};

  programations: any[] = [];

  @Input() meta = {
    agenda: {} as Agenda | null,
    annexe: {} as AnnexeAnatt | null,
  };

  constructor(private dateService: DateService) {}
  ngOnInit(): void {
    const prog: any[] = [];
    for (const date_compo in this.programs) {
      if (Object.prototype.hasOwnProperty.call(this.programs, date_compo)) {
        const top = {
          date_compo: date_compo,
          group: [] as any,
        };
        //Récupération de la liste par permis
        const lists = this.programs[date_compo];

        for (const p in lists) {
          if (Object.prototype.hasOwnProperty.call(lists, p)) {
            const tab = lists[p];
            const permis = {
              name: p,
              list: tab,
            };
            top.group.push(permis);
          }
        }
        prog.push(top);
      }
    }
    this.programations = prog;
  }
  toHumanDate(at: string | undefined, withDays: boolean = false) {
    let d = new Date();
    if (at) {
      d = new Date(at);
    }

    const smonth = this.dateService.getFullMonthFromDay(d.getMonth());
    const y = d.getFullYear();
    let dat = `${d.getDate()} ${smonth} ${y}`;

    if (withDays) {
      const sd = this.dateService.getHumanDay(d.getDay() - 1);
      dat = `${sd} ${dat}`;
    }
    return dat;
  }

  getPermisName(p: any) {
    return Object.keys(p)[0];
  }

  getList(p: any) {
    return Object.values(p);
  }
}
