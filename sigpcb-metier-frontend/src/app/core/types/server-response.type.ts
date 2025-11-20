type ServerErrorsType = {
  [key: string]: string[] | string;
};
export type ServerResponseType = {
  data?: any;
  message?: string;
  status?: boolean;
  errors?: ServerErrorsType;
};
