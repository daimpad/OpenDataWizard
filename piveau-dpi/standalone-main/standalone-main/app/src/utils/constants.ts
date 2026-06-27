export const DEFAULT_STATS = {
  numOfDatasets: 1100,
  numOfDistributions: 2000,
  numOfPublishers: 100,
} as const;

export const DEFAULT_VISUALIZATION_STATS = [
  {value: 475, short: 'ENVI', long: 'Umwelt'},
  {value: 228, short: 'GOVE', long: 'Regierung und öffentlicher Sektor'},
  {value: 172, short: 'REGI', long: 'Regionen und Städte'},
  {value: 561, short: 'TECH', long: 'Wissenschaft und Technologie'},
  {value: 119, short: 'ENER', long: 'Energie'},
  {value: 121, short: 'TRAN', long: 'Verkehr'},
  {
    value: 68,
    short: 'AGRI',
    long: 'Landwirtschaft, Fischerei, Forstwirtschaft und Nahrungsmittel'
  },
  {value: 19, short: 'ECON', long: 'Wirtschaft und Finanzen'},
  {value: 3, short: 'HEAL', long: 'Gesundheit'},
  {value: 4, short: 'EDUC', long: 'Bildung, Kultur und Sport'},
  {value: 3, short: 'SOCI', long: 'Bevölkerung und Gesellschaft'},
  {value: 0, short: 'INTR', long: 'Internationale Themen'},
  {value: 0, short: 'JUST', long: 'Justiz, Rechtssystem und öffentliche Sicherheit'}
] as const;

export const KNOWN_ODP_CATALOG_IDS = [
  'augsburg',
]

export const KNOWN_DISALLOWED_CATALOG_IDS = [
  'open',
  'odb',
  'localhost',
  'www',
  'staging',
  'review',
  'twin',
  'dpiv3',
]
