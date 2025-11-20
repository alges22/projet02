import {
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';
import { CategoryPermisService } from '../../services/category-permis.service';
import { HttpErrorHandlerService } from '../../services/http-error-handler.service';
import { CategoryPermis } from '../../interfaces/catgory-permis';

@Component({
  selector: 'app-permis-list',
  templateUrl: './permis-list.component.html',
  styleUrls: ['./permis-list.component.scss'],
})
export class PermisListComponent implements OnInit, OnChanges {
  permis: CategoryPermis[] = [];
  @Input() multiple = false;
  @Input() selected: number | null = null;
  @Input() multiples: number[] = [];
  @Output() selectEvent = new EventEmitter<CategoryPermis | CategoryPermis[]>();
  constructor(
    private cpService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit() {
    this.cpService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.permis = response.data;
      });
  }

  ngOnChanges(changes: SimpleChanges) {
    if (!this.multiple) {
      if (changes['selected'] && !changes['selected'].firstChange) {
        this.onSelected(changes['selected'].currentValue);
      }
    } else {
      if (changes['multiples'] && !changes['multiples'].firstChange) {
        this.multiples = changes['multiples'].currentValue;
        //create permis object, based on the list of permis ID in this.multiples
        const selectedPermis = this.permis.filter((pm) =>
          this.multiples.includes(pm.id)
        );
        this.selectEvent.emit(selectedPermis);
      }
    }
  }
  onSelected(pmId: number) {
    if (!this.multiple) {
      const oldSelected = this.selected;
      if (oldSelected === pmId) {
        this.selected = null;
      } else {
        this.selected = pmId;
      }
      const pmFound = this.permis.find((pm) => pm.id === this.selected);
      //Parage le permis sélectionné avec le component parent
      this.selectEvent.emit(pmFound);
    } else {
      const index = this.multiples.indexOf(pmId);
      if (index === -1) {
        // Add the pmId to the multiples array
        this.multiples.push(pmId);
      } else {
        // Remove the pmId from the multiples array
        this.multiples.splice(index, 1);
      }
      //create permis object, based on the list of permis ID in this.multiples
      const selectedPermis = this.permis.filter((pm) =>
        this.multiples.includes(pm.id)
      );
      this.selectEvent.emit(selectedPermis);
    }
  }
  isSelected(pmId: number): boolean {
    if (!this.multiple) {
      return pmId === this.selected;
    } else {
      return this.multiples.includes(pmId);
    }
  }
}
