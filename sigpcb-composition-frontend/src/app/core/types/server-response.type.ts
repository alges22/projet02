import { Question } from '../interfaces/question';

type ServerErrorsType = {
  [key: string]: string[] | string;
};
export type ServerResponseType<T = any> = {
  data: T;
  message?: string;
  status?: boolean;
  errors?: ServerErrorsType;
};

export type CurrentQuestion = {
  index: number;
  toCompose: number | null;
  question: Question | null;
  completed: boolean;
  total: number;
  progression: number;
};
