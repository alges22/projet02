import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'hdate',
})
export class HdatePipe implements PipeTransform {
  transform(value: string, withHour: boolean = false): string {
    const date = new Date(value);

    const d = `${date.getUTCDate()}/${
      date.getUTCMonth() + 1
    }/${date.getUTCFullYear()}`;
    if (withHour) {
      const hour = date.getUTCHours();
      const minute = date.getUTCMinutes();
      const formattedTime = `${hour}H:${minute < 10 ? '0' : ''}${minute}`;
      return `${d}, à ${formattedTime}`;
    }

    return d.toLocaleUpperCase();
  }
}
