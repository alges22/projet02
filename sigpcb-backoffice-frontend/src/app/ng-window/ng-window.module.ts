import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgWindowComponent } from './ng-window.component';
import { NgWindowHeaderComponent } from './components/ng-window-header/ng-window-header.component';

import { NgWindowDirective } from './directives/ng-window.directive';
import { NgWindowPageComponent } from './components/ng-window-page/ng-window-page.component';
import { NgWindowBtnComponent } from './components/ng-window-btn/ng-window-btn.component';
import { NgWindowFooterComponent } from './components/ng-window-footer/ng-window-footer.component';

@NgModule({
  declarations: [
    NgWindowComponent,
    NgWindowHeaderComponent,
    NgWindowDirective,
    NgWindowPageComponent,
    NgWindowBtnComponent,
    NgWindowFooterComponent,
  ],
  imports: [CommonModule],
  exports: [
    NgWindowComponent,
    NgWindowHeaderComponent,
    NgWindowDirective,
    NgWindowPageComponent,
    NgWindowBtnComponent,
    NgWindowFooterComponent,
  ],
})
export class NgWindowModule {}
