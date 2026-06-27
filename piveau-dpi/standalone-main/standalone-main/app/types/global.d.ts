export type DistributionFormat = {
  id?: 'default' | 'html' | 'json' | 'xml' | 'txt' | 'csv' | 'xls' | 'zip' | 'api' | 'pdf' | 'shp' | 'ld' | 'rdf' | 'nquad' | 'ntriples' | 'turtle';
  label?: string;
  resource?: string;
};

export type Dataset = {
  /**
   * Dataset title
   */
  title?: string;

  /**
   * Dataset description
   */
  description?: string;

  /**
   * Catalog description
   */
  catalog?: string;

  /**
   * Distribution format hints
   */
  formats?: DistributionFormat[] | null;

  createdDate?: string;
  updatedDate?: string;

  // todo: information about modified and created date
}
