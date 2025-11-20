interface LanguageData {
  name: string;
  count: number;
}

interface GenderData {
  name: string;
  count: number;
}

export interface StatRapport {
  name: string;
  langues: LanguageData[];
  sexes: GenderData[];
  total: number;
  percent: number;
}

export interface StatRapportCode {
  permis: {
    id: number;
    name: string;
    extensions: any[];
  };
  sexes: GenderData;
  admins: GenderData;
  echoues: GenderData;
  total_admis: number;
}
