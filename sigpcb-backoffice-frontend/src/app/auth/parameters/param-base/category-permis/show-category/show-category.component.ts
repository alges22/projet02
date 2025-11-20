import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Component, Input, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { data } from 'jquery';

@Component({
  selector: 'app-show-category',
  templateUrl: './show-category.component.html',
  styleUrls: ['./show-category.component.scss'],
})
export class ShowCategoryComponent implements OnInit {
  page_title = '';

  category!: CategoryPermis;
  extensions: CategoryPermis[] = [];

  constructor(
    private route: ActivatedRoute,
    private categoryService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.getCategory();
  }
  private getCategory() {
    this.errorHandler.startLoader();
    const paramValue = this.route.snapshot.paramMap.get('id') as any;

    this.categoryService
      .findById(paramValue)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.category = response.data;
        this.page_title = `Catégorie de permis : <b>${this.category.name}</b>`;
        this.errorHandler.stopLoader();
        this.setExtensions();
      });
  }

  private setExtensions() {
    this.categoryService
      .getExtensions()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (Array.isArray(this.category.extensions)) {
          this.extensions = this.category.extensions.map((extension: any) => {
            const ext = response.data.find(
              (x: any) => x.id === extension.categorie_permis_extensible_id
            );

            return ext;
          });
        }
      });
  }
}
