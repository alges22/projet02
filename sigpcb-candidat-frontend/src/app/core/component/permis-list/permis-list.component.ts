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
  @Input() permis: CategoryPermis[] = [];
  @Input() multiple = false;
  selected: number | undefined = undefined;
  @Input() default: number | undefined | null = undefined;
  @Input()
  multiples: number[] = [];
  @Output() selectEvent = new EventEmitter<
    CategoryPermis | CategoryPermis[] | undefined
  >();
  constructor(
    private cpService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit() {
    // this.errorHandler.startLoader();
    // this.cpService
    //   .all()
    //   .pipe(
    //     this.errorHandler.handleServerErrors((error: any) => {
    //       this.errorHandler.stopLoader();
    //     })
    //   )
    //   .subscribe((response) => {
    //     this.permis = response.data;
    //     this.errorHandler.stopLoader();
    //   });
    if (this.default) {
      this.onSelected(this.default);
    }
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
    let pmFound = undefined;
    if (!this.multiple) {
      const oldSelected = this.selected;
      if (oldSelected === pmId) {
        this.selected = 1;
      } else {
        this.selected = pmId;
      }
      pmFound = this.permis.find((pm) => pm.id === this.selected);
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
      pmFound = this.permis.filter((pm) => this.multiples.includes(pm.id));
    }
    this.selectEvent.emit(pmFound);
  }
  isSelected(pmId: number): boolean {
    if (!this.multiple) {
      return pmId === this.selected;
    } else {
      return this.multiples.includes(pmId);
    }
  }
}
