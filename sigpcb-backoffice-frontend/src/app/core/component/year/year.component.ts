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
  selector: 'year',
  templateUrl: './year.component.html',
  styleUrls: ['./year.component.scss'],
})
export class YearComponent implements OnInit, OnChanges {
  start = 2020;
  @Input() year: number | null = null;
  @Output('_change') onChange = new EventEmitter<number | null>();
  years: number[] = [];
  ngOnInit(): void {
    const date = new Date();
    const anneeCourante = date.getFullYear() + 1;

    for (let year = this.start; year <= anneeCourante; year++) {
      this.years.push(year);
    }
  }

  ngOnChanges(changes: SimpleChanges): void {}

  onSelect() {
    this.onChange.emit(this.year);
  }
}
