import { Component } from '@angular/core';
import { AuthService } from '../../services/auth.service';
import { redirectTo } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-blue-aside-entreprise',
  templateUrl: './blue-aside-entreprise.component.html',
  styleUrls: ['./blue-aside-entreprise.component.scss'],
})
export class BlueAsideEntrepriseComponent {
  constructor(private authService: AuthService) {}
  url: any;
  ngOnInit(): void {
    this.url = window.location.pathname;
  }
  userConnected() {
    if (this.authService.checked()) {
      return true;
    }
    return false;
  }
  goto(path: any) {
    redirectTo(path, 0);
  }
}
