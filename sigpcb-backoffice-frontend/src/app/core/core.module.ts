import { CalendarPogressComponent } from './component/calendar-pogress/calendar-pogress.component';
import { FormsModule } from '@angular/forms';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalComponent } from './component/modal/modal.component';
import { BuilbeDirective } from './directives/builbe.directive';
import { IconComponent } from './component/icon/icon.component';
import { DropdownComponent } from './component/dropdown/dropdown.component';
import { DropdownDirective } from './directives/dropdown.directive';
import { NeedValidationsDirective } from './directives/need-validations.directive';
import { AlertDirective } from './directives/alert.directive';
import { AlertComponent } from './component/alert/alert.component';
import { SwitchComponent } from './component/switch/switch.component';
import { SearchComponent } from './component/search/search.component';
import { DeleteComponent } from './component/delete/delete.component';
import { LoadingDirective } from './directives/loading.directive';
import { MdLoaderComponent } from './component/md-loader/md-loader.component';
import { LoaderDirective } from './directives/loader.directive';
import { PaginationComponent } from './component/pagination/pagination.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { BreadcrumbComponent } from './component/breadcrumb/breadcrumb.component';
import { NoAutocompleteDirective } from './directives/no-autocomplete.directive';
import { QuickvModule } from '../quickv/quickv.module';
import { BackComponent } from './component/back/back.component';
import { RouterModule } from '@angular/router';
import { TabComponent } from './tab/tab.component';
import { TabHeaderComponent } from './tab/tab-header/tab-header.component';
import { TabBodyComponent } from './tab/tab-body/tab-body.component';
import { PaddingPipe } from './pipes/padding.pipe';
import { AnattDocComponent } from './component/anatt-doc/anatt-doc.component';
import { MonthInputComponent } from './component/month-input/month-input.component';
import { HdatePipe } from './pipes/hdate.pipe';
import { ResultatListComponent } from './component/resultat-list/resultat-list.component';
import { AnattDocConduiteComponent } from './component/anatt-doc-conduite/anatt-doc-conduite.component';
import { HourComponent } from './component/hour/hour.component';
import { AnnexeSessionComponent } from './component/annexe-session/annexe-session.component';
import { SwitchModalComponent } from './component/switch-modal/switch-modal.component';
import { YearComponent } from './component/year/year.component';
import { SingleInputsComponent } from './component/single-inputs/single-inputs.component';
import { BadgeComponent } from './component/badge/badge.component';
import { PopupComponent } from './component/popup/popup.component';
import { PopupBodyComponent } from './component/popup-body/popup-body.component';
import { PopupFooterComponent } from './component/popup-footer/popup-footer.component';

@NgModule({
  declarations: [
    ModalComponent,
    BuilbeDirective,
    IconComponent,
    DropdownComponent,
    DropdownDirective,
    NeedValidationsDirective,
    AlertDirective,
    AlertComponent,
    SwitchComponent,
    SearchComponent,
    DeleteComponent,
    LoadingDirective,
    MdLoaderComponent,
    LoaderDirective,
    PaginationComponent,
    BreadcrumbComponent,
    TabComponent,
    TabHeaderComponent,
    TabBodyComponent,
    NoAutocompleteDirective,
    BackComponent,
    PaddingPipe,
    AnattDocComponent,
    CalendarPogressComponent,
    MonthInputComponent,
    HdatePipe,
    ResultatListComponent,
    AnattDocConduiteComponent,
    HourComponent,
    AnnexeSessionComponent,
    SwitchModalComponent,
    YearComponent,
    SingleInputsComponent,
    BadgeComponent,
    PopupComponent,
    PopupBodyComponent,
    PopupFooterComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    NgxPaginationModule,

    QuickvModule,
  ],
  exports: [
    ModalComponent,
    BuilbeDirective,
    IconComponent,
    DropdownComponent,
    DropdownDirective,
    NeedValidationsDirective,
    AlertDirective,
    AlertComponent,
    SwitchComponent,
    SwitchModalComponent,
    SearchComponent,
    DeleteComponent,
    MdLoaderComponent,
    PaginationComponent,
    BreadcrumbComponent,
    TabComponent,
    TabHeaderComponent,
    TabBodyComponent,
    NoAutocompleteDirective,
    AnattDocComponent,
    AnattDocConduiteComponent,
    PaddingPipe,
    QuickvModule,
    CalendarPogressComponent,
    MonthInputComponent,
    ResultatListComponent,
    HdatePipe,
    HourComponent,
    AnnexeSessionComponent,
    YearComponent,
    SingleInputsComponent,
    BadgeComponent,
    PopupComponent,
    PopupBodyComponent,
    PopupFooterComponent,
  ],
})
export class CoreModule {}
