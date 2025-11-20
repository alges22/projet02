import { FormsModule } from '@angular/forms';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalComponent } from './component/modal/modal.component';
import { IconComponent } from './component/icon/icon.component';
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

import { BreadcrumbComponent } from './component/breadcrumb/breadcrumb.component';
import { NoAutocompleteDirective } from './directives/no-autocomplete.directive';
import { QuickvModule } from '../quickv/quickv.module';
import { PrestationModule } from './prestation/prestation.module';
import { BlueAsideComponent } from './component/blue-aside/blue-aside.component';
import { NumberBoxComponent } from './component/number-box/number-box.component';
import { BlueAsideLeftComponent } from './component/blue-aside-left/blue-aside-left.component';
import { BlueAsideRightComponent } from './component/blue-aside-right/blue-aside-right.component';
import { SingleInputsComponent } from './component/single-inputs/single-inputs.component';
import { AvatarComponent } from './component/avatar/avatar.component';
import { PermisComponent } from './component/permis/permis.component';
import { PermisListComponent } from './component/permis-list/permis-list.component';
import { InputFileComponent } from './components/input-file/input-file.component';
import { NgxExtendedPdfViewerModule } from 'ngx-extended-pdf-viewer';
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
import { DownloadComponent } from './component/download/download.component';
import { UcfirstPipe } from './pipes/ucfirst.pipe';
import { LimitPipe } from './pipes/limit.pipe';
import { AutoEcoleComponent } from './component/auto-ecole/auto-ecole.component';
import { DateCounterComponent } from './component/date-counter/date-counter.component';
import { DropdownDirective } from './directives/dropdown.directive';
import { SidebarComponent } from './component/sidebar/sidebar.component';

@NgModule({
  declarations: [
    ModalComponent,
    IconComponent,
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
    NoAutocompleteDirective,
    BlueAsideComponent,
    NumberBoxComponent,
    BlueAsideLeftComponent,
    BlueAsideRightComponent,
    SingleInputsComponent,
    AvatarComponent,
    PermisComponent,
    PermisListComponent,
    InputFileComponent,
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
    UcfirstPipe,
    LimitPipe,
    AutoEcoleComponent,
    DateCounterComponent,
    DropdownDirective,
    SidebarComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    NgxPaginationModule,
    NgxExtendedPdfViewerModule,
    QuickvModule,
    RouterModule,
  ],
  exports: [
    ModalComponent,
    IconComponent,
    NeedValidationsDirective,
    AlertDirective,
    AlertComponent,
    SwitchComponent,
    SearchComponent,
    DeleteComponent,
    MdLoaderComponent,
    PaginationComponent,
    BreadcrumbComponent,
    NoAutocompleteDirective,
    BlueAsideComponent,
    NumberBoxComponent,
    BlueAsideLeftComponent,
    BlueAsideRightComponent,
    SingleInputsComponent,
    InputFileComponent,
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
    PrestationModule,
    TdatePipe,
    SuiviDetailsComponent,
    StatCardComponent,
    UcfirstPipe,
    LimitPipe,
    DropdownDirective,
    AutoEcoleComponent,
    SidebarComponent,
  ],
})
export class CoreModule {}
