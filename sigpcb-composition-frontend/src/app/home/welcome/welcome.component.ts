import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { StateService } from 'src/app/core/services/state.service';
import { StorageService } from 'src/app/core/services/storage.service';

@Component({
  selector: 'app-welcome',
  templateUrl: './welcome.component.html',
  styleUrls: ['./welcome.component.scss'],
})
export class WelcomeComponent implements OnInit {
  constructor(
    private storage: StorageService,
    private router: Router,
    private stateService: StateService
  ) {}

  ngOnInit(): void {
    this.storage.destroy();
  }

  login() {
    this.router.navigate(['/login']);
    this.stateService.enterFullScreen();
  }
}
