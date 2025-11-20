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
import { PrestationModule } from './prestation/prestation.module';
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
import { InputFileComponent } from './components/input-file/input-file.component';
import { ConsentCookieComponent } from './component/consent-cookie/consent-cookie.component';
import { HdatePipe } from './pipes/hdate.pipe';
import { InscriptionExamenComponent } from '../auth/dashboard/demandes/inscription-examen/inscription-examen.component';
import { BlueAsideEntrepriseComponent } from './component/blue-aside-entreprise/blue-aside-entreprise.component';
import { SoumissionComponent } from './component/soumission/soumission.component';
import { BlueMoniteurAsideComponent } from './component/blue-moniteur-aside/blue-moniteur-aside.component';
import { FooterComponent } from './component/footer/footer.component';
// import { PdfViewerModule } from 'ng2-pdf-viewer';

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
    HdatePipe,
    BlueAsideEntrepriseComponent,
    SoumissionComponent,
    BlueMoniteurAsideComponent,
    FooterComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    NgxPaginationModule,
    // NgxExtendedPdfViewerModule,
    QuickvModule,
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
    DeleteComponent,
    MdLoaderComponent,
    PaginationComponent,
    BreadcrumbComponent,
    NoAutocompleteDirective,
    BlueAsideComponent,
    BlueAsideEntrepriseComponent,
    NumberBoxComponent,
    BlueAsideLeftComponent,
    BlueAsideRightComponent,
    SingleInputsComponent,
    InputFileComponent,
    AvatarComponent,
    PermisComponent,
    PermisListComponent,
    ConsentCookieComponent,
    QuickvModule,
    PrestationModule,
    TranslateModule,
    HdatePipe,
    SoumissionComponent,
    BlueMoniteurAsideComponent,
    FooterComponent,
  ],
})
export class CoreModule {}
