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
import { LoadingDirective } from './directives/loading.directive';
import { MdLoaderComponent } from './component/md-loader/md-loader.component';
import { LoaderDirective } from './directives/loader.directive';
import { PaginationComponent } from './component/pagination/pagination.component';

import { BreadcrumbComponent } from './component/breadcrumb/breadcrumb.component';
import { NoAutocompleteDirective } from './directives/no-autocomplete.directive';
import { QuickvModule } from '../quickv/quickv.module';
import { BlueAsideComponent } from './component/blue-aside/blue-aside.component';
import { NumberBoxComponent } from './component/number-box/number-box.component';
import { BlueAsideLeftComponent } from './component/blue-aside-left/blue-aside-left.component';
import { BlueAsideRightComponent } from './component/blue-aside-right/blue-aside-right.component';
import { SingleInputsComponent } from './component/single-inputs/single-inputs.component';
import { AvatarComponent } from './component/avatar/avatar.component';
import { PermisComponent } from './component/permis/permis.component';
import { PermisListComponent } from './component/permis-list/permis-list.component';
import { ConsentCookieComponent } from './component/consent-cookie/consent-cookie.component';
import { RouterModule } from '@angular/router';
import { CalendarPogressComponent } from './component/calendar-pogress/calendar-pogress.component';
import { TabComponent } from './component/tab/tab.component';
import { TabHeaderComponent } from './component/tab/tab-header/tab-header.component';
import { TabBodyComponent } from './component/tab/tab-body/tab-body.component';
import { FicheCandidatComponent } from './component/fiche-candidat/fiche-candidat.component';
import { PaddingPipe } from './pipes/padding.pipe';
import { TdatePipe } from './pipes/tdate.pipe';
import { SuiviDetailsComponent } from './component/suivi-details/suivi-details.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { StatCardComponent } from './component/stat-card/stat-card.component';
import { FooterComponent } from './component/footer/footer.component';
import { HeaderComponent } from './component/header/header.component';
import { ScannerComponent } from './component/scanner/scanner.component';
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
    LoadingDirective,
    MdLoaderComponent,
    LoaderDirective,
    PaginationComponent,
    BreadcrumbComponent,
    NoAutocompleteDirective,
    BlueAsideComponent,
    NumberBoxComponent,
    BlueAsideLeftComponent,
    BlueAsideRightComponent,
    SingleInputsComponent,
    AvatarComponent,
    PermisComponent,
    PermisListComponent,
    ConsentCookieComponent,
    CalendarPogressComponent,
    TabComponent,
    TabHeaderComponent,
    TabBodyComponent,
    FicheCandidatComponent,
    PaddingPipe,
    TdatePipe,
    SuiviDetailsComponent,
    StatCardComponent,
    FooterComponent,
    HeaderComponent,
    ScannerComponent,
    PopupComponent,
    PopupBodyComponent,
    PopupFooterComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    NgxPaginationModule,
    QuickvModule,
    RouterModule,
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
    SearchComponent,
    MdLoaderComponent,
    PaginationComponent,
    BreadcrumbComponent,
    NoAutocompleteDirective,
    BlueAsideComponent,
    NumberBoxComponent,
    BlueAsideLeftComponent,
    BlueAsideRightComponent,
    SingleInputsComponent,
    AvatarComponent,
    PermisComponent,
    PermisListComponent,
    ConsentCookieComponent,
    CalendarPogressComponent,
    TabComponent,
    TabHeaderComponent,
    TabBodyComponent,
    PaddingPipe,
    FicheCandidatComponent,
    QuickvModule,
    TdatePipe,
    SuiviDetailsComponent,
    StatCardComponent,
    FooterComponent,
    HeaderComponent,
    ScannerComponent,
    PopupComponent,
    PopupBodyComponent,
    PopupFooterComponent,
  ],
})
export class CoreModule {}
