import { describe, it, expect } from 'vitest'
import { getSubdomainCatalogIdFromUrl, getLeftmostSubdomain } from './utils.ts'

describe('utils', () => {
  describe('getSubdomainCatalogIdFromUrl', () => {
    it('should return the subdomain catalog ID from the URL', () => {
      const url = 'https://id.example.com';
      const disallowedCatalogIds = ['admin', 'test'];
      const expectedCatalogId = 'id';
  
      const result = getSubdomainCatalogIdFromUrl(url, disallowedCatalogIds);
  
      expect(result).toBe(expectedCatalogId);
    });
  
    it('should return an empty string if the subdomain catalog ID is disallowed', () => {
      const url = 'https://admin.example.com';
      const disallowedCatalogIds = ['admin', 'test'];
  
      const result = getSubdomainCatalogIdFromUrl(url, disallowedCatalogIds);
  
      expect(result).toBe('');
    });
  
    it('should return an empty string if the URL does not have a subdomain', () => {
      const url = 'https://example.com';
      const disallowedCatalogIds = ['admin', 'test'];
  
      const result = getSubdomainCatalogIdFromUrl(url, disallowedCatalogIds);
  
      expect(result).toBe('');
    });
  });

  describe('getLeftmostSubdomain', () => {
    it('should return the leftmost subdomain for a simple domain', () => {
      const hostname = 'augsburg.bydata.de';
      const expectedSubdomain = 'augsburg';

      const result = getLeftmostSubdomain(hostname);

      expect(result).toBe(expectedSubdomain);
    });
    
    it('should return the leftmost subdomain for a deeply nested domain', () => {
      const hostname = 'augsburg.staging.bydata.de';
      const expectedSubdomain = 'augsburg';

      const result = getLeftmostSubdomain(hostname);

      expect(result).toBe(expectedSubdomain);
    });

    it('should return an empty string for a domain without subdomain', () => {
      const hostname = 'example.com';

      const result = getLeftmostSubdomain(hostname);

      expect(result).toBe('');
    });

    it('should return the subdomain for a .local domain', () => {
      const hostname = 'augsburg.local';
      const expectedSubdomain = 'augsburg';

      const result = getLeftmostSubdomain(hostname);

      expect(result).toBe(expectedSubdomain);
    });

    it('should return an empty string for a domain with only www subdomain', () => {
      const hostname = 'www.example.com';

      const result = getLeftmostSubdomain(hostname);

      expect(result).toBe('');
    });
  });
})