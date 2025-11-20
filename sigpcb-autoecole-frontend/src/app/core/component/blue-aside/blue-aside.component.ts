import { Component, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { AutoEcole } from '../../interfaces/user.interface';

@Component({
  selector: 'app-blue-aside',
  templateUrl: './blue-aside.component.html',
  styleUrls: ['./blue-aside.component.scss'],
})
export class BlueAsideComponent {
  @Input('auth') auth: any = null;
  _toggleSidebar = false;

  ngOnInit(): void {}
  toggleSidebar() {
    this._toggleSidebar = !this._toggleSidebar;
  }
}
