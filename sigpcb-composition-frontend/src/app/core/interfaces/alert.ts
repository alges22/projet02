export type AlertType = 'warning' | 'danger' | 'success';

export type AlertPosition =
  | 'top-left'
  | 'middle'
  | 'top-right'
  | 'bottom-left'
  | 'bottom-right';

export interface IAlert {
  type: AlertType;
  position: AlertPosition;
  message: string;
  timeout?: number;
  fixed?: boolean;
}
