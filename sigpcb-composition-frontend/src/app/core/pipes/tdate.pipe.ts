import { DatePipe } from '@angular/common';
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'tdate',
})
export class TdatePipe implements PipeTransform {
  transform(value: any, args?: any): any {
    const date = new Date(value);

    const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    const day = dayNames[date.getUTCDay()];

    const dayOfMonth = date.getUTCDate();
    const month = date.toLocaleString('default', { month: 'short' });

    const year = date.getUTCFullYear().toString().substr(-2);

    return `${day} ${dayOfMonth}, ${year}`;
  }
}
