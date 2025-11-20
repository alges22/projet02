import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-number-box',
  templateUrl: './number-box.component.html',
  styleUrls: ['./number-box.component.scss'],
})
export class NumberBoxComponent {
  @Input() number: number | null = null;
  @Input() filled = false;
}
