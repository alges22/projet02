import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-md-loader',
  templateUrl: './md-loader.component.html',
  styleUrls: ['./md-loader.component.scss'],
})
export class MdLoaderComponent {
  @Input() message = 'Chargement en cours';
  @Input() show = true;
}
