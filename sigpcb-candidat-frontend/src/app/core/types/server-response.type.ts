type ServerErrorsType = {
  [key: string]: string[] | string;
};
export type ServerResponseType<T = any> = {
  data?: T;
  message?: string;
  status?: boolean;
  errors?: ServerErrorsType;
};
