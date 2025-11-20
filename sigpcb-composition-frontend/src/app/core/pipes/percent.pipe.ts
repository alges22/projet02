import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'percent',
})
export class PercentPipe implements PipeTransform {
  transform(value: number, total: number, ...args: unknown[]): unknown {
    if (total === 0) {
      return 0;
    }
    return (value / total) * 100;
  }
}
