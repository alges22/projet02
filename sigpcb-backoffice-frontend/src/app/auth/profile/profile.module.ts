import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ProfileRoutingModule } from './profile-routing.module';
import { ProfileComponent } from './profile.component';
import { ProfileHomeComponent } from './profile-home/profile-home.component';
import { CoreModule } from 'src/app/core/core.module';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [ProfileComponent, ProfileHomeComponent],
  imports: [CommonModule, ProfileRoutingModule, FormsModule, CoreModule],
})
export class ProfileModule {}
