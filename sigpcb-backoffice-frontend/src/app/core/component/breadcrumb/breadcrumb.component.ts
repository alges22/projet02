import { Component } from '@angular/core';
import { Breadcrumb } from '../../interfaces/breadcrumb';
import { BreadcrumbService } from '../../services/breadcrumb.service';

@Component({
  selector: 'app-breadcrumb',
  templateUrl: './breadcrumb.component.html',
  styleUrls: ['./breadcrumb.component.scss'],
})
export class BreadcrumbComponent {
  breadcrumbs: Breadcrumb[] = [];
  titleBreadcrumb: string = '';
  currentBreadcrumb: Breadcrumb | undefined = undefined;
  constructor(private breadcrumbService: BreadcrumbService) {}
  ngOnInit(): void {
    this.__setBreadcrumb();
  }

  private __setBreadcrumb() {
    this.breadcrumbService.getBreadcrumbs().subscribe((brds) => {
      this.breadcrumbs = [];
      for (let i = 0; i < brds.length; i++) {
        const b = brds[i];
        if (!b.active) {
          this.breadcrumbs.push(b);
        } else {
          this.currentBreadcrumb = b;
        }
      }
    });

    this.breadcrumbService.getTitle().subscribe((title) => {
      this.titleBreadcrumb = title;
    });
  }
}
