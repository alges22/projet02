import { AfterViewInit, Component, EventEmitter, Output } from '@angular/core';
import { ProfileData } from '../../interfaces/profiles';
import { ProfileService } from '../../services/profile.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-compo-layout',
  templateUrl: './compo-layout.component.html',
  styleUrls: ['./compo-layout.component.scss'],
})
export class CompoLayoutComponent implements AfterViewInit {
  @Output() onProfile = new EventEmitter();
  profileData: ProfileData | null = null;
  constructor(private profileService: ProfileService, private router: Router) {}
  ngAfterViewInit(): void {}
  ngOnInit() {
    this.getProfile();
  }
  getProfile() {
    this.profileService.get().subscribe((profileData) => {
      if (profileData) {
        this.profileData = profileData;
        if (profileData) {
        }
        this.onProfile.emit(this.profileData);
      }
    });
  }
}
