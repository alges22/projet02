import { Component } from '@angular/core';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss'],
})
export class HeaderComponent {
  authentificated: boolean = false;
  constructor(private authService: AuthService) {}

  ngOnInit(): void {
    this.authentificated = this.authService.checked();
  }
}
