import * as z from 'zod'

/**
 * Add trailing slash to url if it doesn't have one
 */
function addTrailingSlash(url: string) {
  if (url.endsWith('/'))
    return url

  return `${url}/`
}

export const apiSchema = z.object({
  baseUrl: z.string().url().transform(addTrailingSlash),
  hubUrl: z.string().url().transform(addTrailingSlash),
  qualityBaseUrl: z.string().url().transform(addTrailingSlash),
  similarityBaseUrl: z.string().url().transform(addTrailingSlash),
  similarityServiceName: z.string().default('similarities'),
  fileUploadUrl: z.string().url().transform(addTrailingSlash),
  sparqlUrl: z.string().url().transform(addTrailingSlash),
  gazetteerBaseUrl: z.string().url().transform(addTrailingSlash),
  corsproxyApiUrl: z.string().url().transform(addTrailingSlash),
})
