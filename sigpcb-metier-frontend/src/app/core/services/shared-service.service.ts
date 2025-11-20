import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class SharedServiceService {
  constructor() {}
  isSelected: boolean = false;

  updateIsSelected(isSelected: boolean) {
    this.isSelected = isSelected;
  }
}
