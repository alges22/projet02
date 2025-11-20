import { Pipe, PipeTransform } from '@angular/core';
import { numberPad } from 'src/app/helpers/helpers';

@Pipe({
  name: 'padding',
})
export class PaddingPipe implements PipeTransform {
  transform(
    value: string | number | null | undefined,
    length: number = 2,
    padChar: string = '0'
  ): string | number {
    if (value === undefined || value === null) {
      return '';
    }

    if (isNaN(Number(value))) {
      return value;
    }
    return numberPad(value, length, padChar);
  }
}
