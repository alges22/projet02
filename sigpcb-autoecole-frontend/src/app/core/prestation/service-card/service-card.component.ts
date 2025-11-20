import { Component, Input } from '@angular/core';
import { Prestation } from '../interface/prestation';

@Component({
  selector: 'app-service-card',
  templateUrl: './service-card.component.html',
  styleUrls: ['./service-card.component.scss'],
})
export class ServiceCardComponent {
  @Input() prestation!: Prestation;
  @Input() link = '';
  @Input() user: 'moniteur' | 'promoteur' = 'promoteur';
}
