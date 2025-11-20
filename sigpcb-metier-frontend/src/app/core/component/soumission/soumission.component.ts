import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-soumission',
  templateUrl: './soumission.component.html',
  styleUrls: ['./soumission.component.scss'],
})
export class SoumissionComponent {
  @Input() activateId: number = 0;
  @Input('show-alert') showSoumission = false;
  @Input('show-loader') showLoaderSoumission = false;

  @Output() canSoumission = new EventEmitter<number>();

  ngOnInit(): void {}

  confirmSoumission() {
    this.canSoumission.emit(this.activateId);
    this.showSoumission = false;
  }
  cancelSoumission() {
    this.showSoumission = false;
  }

  openSoumission() {
    this.showSoumission = true;
  }
}
