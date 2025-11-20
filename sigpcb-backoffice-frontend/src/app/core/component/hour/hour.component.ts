import {
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';

@Component({
  selector: 'hour',
  templateUrl: './hour.component.html',
  styleUrls: ['./hour.component.scss'],
})
export class HourComponent implements OnInit, OnChanges {
  hours: number[] = [];
  minutes: number[] = [];
  hour = '00';
  min = '00';
  @Output('select') onChangeEvent = new EventEmitter<string>();
  @Input('hour') hourString = '00:00:00';
  ngOnInit(): void {
    for (let i = 0; i < 24; i++) {
      this.hours.push(i);
    }

    for (let i = 0; i < 60; i++) {
      this.minutes.push(i);
    }
    this.setHours();
  }

  emit() {
    this.onChangeEvent.emit(`${this.hour}:${this.min}:00`);
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['hourString']) {
      this.hourString = changes['hourString'].currentValue || this.hourString;
      this.setHours();
    }
  }
  private setHours() {
    let parts = this.hourString.split(':');
    this.hour = parts[0] || this.hour;
    this.min = parts[1] || this.min;
  }
}
