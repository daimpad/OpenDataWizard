/**
 * @author Dennis ritter
 * @created 06.07.17
 * @description Contains filters to format Dates.
 */

import dayjs from 'dayjs';

/**
 * The String to display when the given String is a invalid date String.
 * @type {String}
 */
const INVALID_DATE_STRING = '';

const dateFilters = {
  setLocale(locale = 'en', formatOptions = {}) {
    dayjs.locale('en');
  },
  /**
   * @description Transforms the given date into a US Date Format String
   * @param {date} date - The given date
   * @returns {String}
   */
  formatUS(date : string) {
    if (date === undefined) return INVALID_DATE_STRING;
    const m = dayjs(String(date));
    const splittedDate = date.split('T');
    if (m.isValid()) {
      if (splittedDate.length === 1) return m.format('MM/DD/YYYY');
      return m.format('MM/DD/YYYY HH:mm');
    }
    return INVALID_DATE_STRING;
  },
  /**
   * @description Transforms the given date into a US Date Format String
   * @param {date} date - The given date
   * @returns {String}
   */
  formatEU(date : string) {
    // Need to be reworked ######### Start ########
    if (String(date)[0] === '_') {
      date = String(date).replace('_','')
    }
     // Need to be reworked ######### End ########
    if (date === undefined) return INVALID_DATE_STRING;
    const m = dayjs(String(date));
    const splittedDate = date.split('T');
    if (m.isValid()) {
      
      if (splittedDate.length === 1) return m.format('DD.MM.YYYY');
      return m.format('DD MMMM YYYY');
    }
    return INVALID_DATE_STRING;
  },
  /**
   * @description Returns a String representing the expired time from the given date to now.
   * @param {date} date - The given date
   * @returns {String}
   */
  fromNow(date : string) {
    if (date === undefined) return INVALID_DATE_STRING;
    const m = dayjs(String(date));
    // @ts-ignore
    if (m.isValid()) return m.fromNow;
    return INVALID_DATE_STRING;
  },
};

export default dateFilters;
