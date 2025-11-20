import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { SearchService } from '../../services/search.service';

@Component({
  selector: 'app-search',
  templateUrl: './search.component.html',
  styleUrls: ['./search.component.scss'],
})
export class SearchComponent implements OnInit {
  @Input() url = '';
  @Input() message = 'Recherche ...';
  is_loading = false;
  search = '';

  @Output() results = new EventEmitter<any>();
  constructor(private searchService: SearchService) {}
  ngOnInit(): void {}

  onInput() {
    if (this.search.length > 2) {
      this.is_loading = true;
      this.searchService.search(this.url, this.search).subscribe((response) => {
        this.results.emit(response);
        this.is_loading = false;
      });
    } else {
      //Sans ceci quand la personne supprime les champs la liste reste sur les derniers chargements
      const resp = {
        status: true,
        data: [],
        refresh: true,
      };
      this.results.emit(resp);
      this.is_loading = false;
    }
  }
}
