import { Component, Input } from '@angular/core';
import { ImageService } from './../../services/image.service';
import { StorageService } from '../../services/storage.service';

@Component({
  selector: 'app-avatar',
  templateUrl: './avatar.component.html',
  styleUrls: ['./avatar.component.scss'],
})
export class AvatarComponent {
  @Input('user') user: any;

  constructor(
    private readonly imageService: ImageService,
    private readonly storage: StorageService
  ) {}

  ngOnInit() {
    this.imageService.user().subscribe((response) => {
      const avatar = response.data.image;
      if (this.user && avatar) {
        this.user['avatar'] = 'data:image/png;base64,' + avatar;
      }
    });
  }
}
