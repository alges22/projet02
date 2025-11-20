import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-blue-aside-right',
  templateUrl: './blue-aside-right.component.html',
  styleUrls: ['./blue-aside-right.component.scss'],
})
export class BlueAsideRightComponent {
  @Input() col = 'col-12';
}
