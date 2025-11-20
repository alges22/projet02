// popup.component.ts
import {
  Component,
  Input,
  Output,
  EventEmitter,
  OnChanges,
  SimpleChanges,
} from '@angular/core';
import {
  trigger,
  state,
  style,
  animate,
  transition,
} from '@angular/animations';

@Component({
  selector: 'popup',
  template: `
    <div class="popup-overlay" [@overlayAnimation]="isOpen ? 'open' : 'closed'">
      <div
        class="popup-container"
        [@modalAnimation]="isOpen ? 'open' : 'closed'"
      >
        <div class="popup-header">
          <h2>{{ title }}</h2>
          <button class="close-button" (click)="close()">
            <span class="close-icon">×</span>
          </button>
        </div>

        <div class="popup-body">
          <ng-content select="popup-body"></ng-content>
        </div>

        <div class="popup-footer">
          <ng-content select="popup-footer"></ng-content>
        </div>
      </div>
    </div>
  `,
  styles: [
    `
      .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 3000;
      }

      .popup-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 14px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        width: 90%;
        max-width: 500px;
        padding: 0;
        position: relative;
        overflow: hidden;
      }

      .popup-header {
        padding: 16px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .popup-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 500;
        color: #1d1d1f;
      }

      .close-button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
      }

      .close-button:hover {
        background-color: rgba(0, 0, 0, 0.05);
      }

      .close-icon {
        font-size: 24px;
        line-height: 1;
        color: rgb(187, 4, 19);
      }

      .popup-body {
        padding: 20px;
        max-height: 70vh;
        overflow-y: auto;
      }

      .popup-footer {
        padding: 16px 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
      }
    `,
  ],
  animations: [
    trigger('overlayAnimation', [
      state(
        'closed',
        style({
          opacity: 0,
          visibility: 'hidden',
        })
      ),
      state(
        'open',
        style({
          opacity: 1,
          visibility: 'visible',
        })
      ),
      transition('closed => open', [animate('200ms ease-out')]),
      transition('open => closed', [animate('150ms ease-in')]),
    ]),
    trigger('modalAnimation', [
      state(
        'closed',
        style({
          transform: 'scale(0.95)',
          opacity: 0,
        })
      ),
      state(
        'open',
        style({
          transform: 'scale(1)',
          opacity: 1,
        })
      ),
      transition('closed => open', [animate('450ms ease-out')]),
      transition('open => closed', [animate('400ms ease-in')]),
    ]),
  ],
})
export class PopupComponent implements OnChanges {
  @Input() title: string = '';
  @Input('open') isOpen: boolean = false;
  @Output('close') onClose = new EventEmitter<void>();

  close() {
    this.isOpen = false;
    this.onClose.emit();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['open']) {
      console.log(this.isOpen);
    }
  }
}
