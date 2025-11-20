export type TransactionResponse = {
  uuid: string;
  amount: number;
  date_payment: string;
  status: 'init' | 'approved';
  url: string;
};
