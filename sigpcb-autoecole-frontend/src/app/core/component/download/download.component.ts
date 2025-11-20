import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-download',
  templateUrl: './download.component.html',
  styleUrls: ['./download.component.scss'],
})
export class DownloadComponent {
  @Input() state = null as
    | 'init'
    | 'pending'
    | 'payment'
    | 'validate'
    | 'rejet'
    | null;
}
