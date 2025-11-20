import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-delete',
  templateUrl: './delete.component.html',
  styleUrls: ['./delete.component.scss'],
})
export class DeleteComponent {
  @Input() activateId: number = 0;
  @Input('show-alert') show = false;
  @Input('show-loader') showLoader = false;

  @Output() canDelete = new EventEmitter<number>();

  ngOnInit(): void {}

  confirmDelete() {
    this.canDelete.emit(this.activateId);
    this.show = false;
  }
  cancel() {
    this.show = false;
  }

  open() {
    this.show = true;
  }
}
