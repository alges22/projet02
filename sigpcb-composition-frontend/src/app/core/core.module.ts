import { FormsModule } from '@angular/forms';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalComponent } from './component/modal/modal.component';
import { IconComponent } from './component/icon/icon.component';
import { AlertComponent } from './component/alert/alert.component';
import { MdLoaderComponent } from './component/md-loader/md-loader.component';

import { RouterModule } from '@angular/router';
import { PaddingPipe } from './pipes/padding.pipe';
import { TdatePipe } from './pipes/tdate.pipe';
import { LayoutComponent } from './component/layout/layout.component';
import { QuestionCheckComponent } from './components/question-check/question-check.component';
import { TimerPipe } from './pipes/timer.pipe';
import { PercentPipe } from './pipes/percent.pipe';
import { BottomBarComponent } from './component/bottom-bar/bottom-bar.component';
import { ScannerComponent } from './components/scanner/scanner.component';
import { ChronoComponent } from './component/chrono/chrono.component';
import { TopBarComponent } from './component/top-bar/top-bar.component';
import { CompoLayoutComponent } from './component/compo-layout/compo-layout.component';

@NgModule({
  declarations: [
    ModalComponent,
    IconComponent,
    AlertComponent,
    MdLoaderComponent,
    PaddingPipe,
    TdatePipe,
    LayoutComponent,
    QuestionCheckComponent,
    TimerPipe,
    PercentPipe,
    BottomBarComponent,
    ScannerComponent,
    ChronoComponent,
    TopBarComponent,
    CompoLayoutComponent,
  ],
  imports: [CommonModule, FormsModule, RouterModule],
  exports: [
    ModalComponent,
    IconComponent,
    AlertComponent,
    MdLoaderComponent,
    PaddingPipe,
    TdatePipe,
    LayoutComponent,
    QuestionCheckComponent,
    TimerPipe,
    PercentPipe,
    BottomBarComponent,
    TopBarComponent,
    ChronoComponent,
    ScannerComponent,
    CompoLayoutComponent,
  ],
})
export class CoreModule {}
