import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'hdate',
})
export class HdatePipe implements PipeTransform {
  transform(value: string, withHour: boolean = false): string {
    const options = {
      weekday: 'short',
      day: 'numeric',
      year: 'numeric',
      month: 'short',
    } as any;
    const date = new Date(value);
    const formattedDate = date.toLocaleDateString('fr-FR', options);

    if (withHour) {
      const hour = date.getHours();
      const minute = date.getMinutes();
      const formattedTime = `${hour}H:${minute < 10 ? '0' : ''}${minute}`;
      return `${formattedDate.toUpperCase()}, à ${formattedTime}`;
    }

    return formattedDate.toLocaleUpperCase();
  }
}
