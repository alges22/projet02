import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';

@Component({
  selector: 'app-pagination',
  templateUrl: './pagination.component.html',
  styleUrls: ['./pagination.component.scss'],
})
export class PaginationComponent implements OnInit {
  @Input() pageNumber = 1;
  @Input() total = 1;

  @Input() perPage = 10;

  totalPage = 1;

  pages: number[] = [];
  @Output() pageChange = new EventEmitter<number>();

  paginate(currentPage: number) {
    this.pageChange.emit(currentPage);
  }

  ngOnInit(): void {
    const totalPage = Math.ceil(this.total / this.perPage);
    this.totalPage = totalPage > 0 ? totalPage : 1;

    for (let i = 1; i <= this.totalPage; i++) {
      this.pages.push(i);
    }
  }

  paginateOnChange(target: any) {
    this.paginate(target.value);
  }
}
