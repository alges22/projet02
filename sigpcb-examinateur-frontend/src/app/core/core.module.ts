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
import { HttpClient } from '@angular/common/http';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { RouterModule } from '@angular/router';
import { TabComponent } from './component/tab/tab.component';
import { TabHeaderComponent } from './component/tab/tab-header/tab-header.component';
import { TabBodyComponent } from './component/tab/tab-body/tab-body.component';
import { FicheCandidatComponent } from './component/fiche-candidat/fiche-candidat.component';
import { PaddingPipe } from './pipes/padding.pipe';
import { TdatePipe } from './pipes/tdate.pipe';
import { ScannerComponent } from './component/scanner/scanner.component';

// AoT requires an exported function for factories
export function HttpLoaderFactory(http: HttpClient) {
  return new TranslateHttpLoader(http);
}

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
    TabComponent,
    TabHeaderComponent,
    TabBodyComponent,
    FicheCandidatComponent,
    PaddingPipe,
    TdatePipe,
    ScannerComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    QuickvModule,
    RouterModule,
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: HttpLoaderFactory,
        deps: [HttpClient],
      },
    }),
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
    TabComponent,
    TabHeaderComponent,
    TabBodyComponent,
    PaddingPipe,
    FicheCandidatComponent,
    QuickvModule,
    TranslateModule,
    TdatePipe,
    ScannerComponent,
  ],
})
export class CoreModule {}
