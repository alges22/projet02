import {
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';
import { FullMonth } from '../../interfaces/date';
import { DateService } from '../../services/date.service';

@Component({
  selector: 'app-month-input',
  templateUrl: './month-input.component.html',
  styleUrls: ['./month-input.component.scss'],
})
export class MonthInputComponent implements OnInit, OnChanges {
  @Input('start') start: FullMonth = 'Janvier';

  months: FullMonth[] = [];
  selected: FullMonth | null = null;
  @Output('monthChange') monthChange = new EventEmitter<FullMonth | null>();
  @Input() default: FullMonth | string | null = null;
  @Input() placeholder = 'Sélectionner ...';
  @Input('disabled') disabled = false;
  constructor(private dateService: DateService) {}

  ngOnInit(): void {
    this.months = this.dateService.getMonthFrom(this.start);

    if (this.default) {
      this.selected = this.default as FullMonth;
    }
  }
  onSelected(): void {
    this.monthChange.emit(this.selected);
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['default'] && !changes['default'].firstChange) {
      // Le changement par rapport à la valeur précédente a été détecté
      const newDefaultValue = changes['default'].currentValue as
        | FullMonth
        | string
        | null;

      if (newDefaultValue) {
        this.selected = newDefaultValue as FullMonth;
      } else {
        this.selected = null;
      }
    }
  }
}
