import { Component } from '@angular/core';
import { ProfileData } from '../core/interfaces/profiles';
import { PageService } from '../core/services/page.service';

@Component({
  selector: 'app-compo',
  templateUrl: './compo.component.html',
  styleUrls: ['./compo.component.scss'],
})
export class CompoComponent {
  profileData: ProfileData | null = null;

  page: string | null = null;
  constructor(private pageService: PageService) {}

  ngOnInit() {}
  setProfileData(profileData: ProfileData | null) {
    this.profileData = profileData;
    this.page = this.profileData?.page ?? this.page;

    this.pageService.onPageChange().subscribe((page) => {
      if (page) {
        this.page = page;
      }
    });
  }
}
