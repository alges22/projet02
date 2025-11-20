import { Agenda } from 'src/app/core/interfaces/examens';
import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import { DateService } from '../../services/date.service';
import { AnnexeAnatt } from '../../interfaces/annexe-anatt';

@Component({
  selector: 'anatt-doc',
  templateUrl: './anatt-doc.component.html',
  styleUrls: ['./anatt-doc.component.scss'],
})
export class AnattDocComponent implements OnInit, OnChanges {
  @Input() programs: Record<string, any> = {};

  programations: any[] = [];

  @Input() meta = {
    agenda: {} as Agenda | null,
    annexe: {} as AnnexeAnatt | null,
  };

  constructor(private dateService: DateService) {}
  ngOnInit(): void {
    this.setup();
  }
  ngOnChanges(changes: SimpleChanges): void {
    if ('programs' in changes) {
      this.programs = changes['programs'].currentValue;
      this.setup();
    }

    if ('meta' in changes) {
      this.meta = changes['meta'].currentValue;
    }
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

  setup() {
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
}
