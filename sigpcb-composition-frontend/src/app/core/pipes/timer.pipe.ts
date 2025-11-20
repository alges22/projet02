import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'timer',
})
export class TimerPipe implements PipeTransform {
  /**
   * Convertir la seconde en "1 min 2 s"
   * @param value
   * @param args
   * @returns
   */
  transform(seconds: number): string {
    if (isNaN(seconds) || seconds < 0) {
      return '';
    }

    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;

    let result = '';
    if (minutes > 0) {
      result += `${minutes} min `;
    }

    if (remainingSeconds > 0) {
      result += `${remainingSeconds} s`;
    }

    return result.trim();
  }
}
