import fs from 'node:fs/promises'
import path from 'node:path'
import process from 'node:process'
import { fileURLToPath } from 'node:url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

// List of all vocabulary URLs to fetch
const vucabularyUris = [
  'https://staging.bydata.de/api/hub/search/vocabularies/iana-media-types',
  'https://staging.bydata.de/api/hub/search/vocabularies/continent',
  'https://staging.bydata.de/api/hub/search/vocabularies/place',
  'https://staging.bydata.de/api/hub/search/vocabularies/contributors',
  'https://staging.bydata.de/api/hub/search/vocabularies/political-geocoding-government-district-key',
  'https://staging.bydata.de/api/hub/search/vocabularies/hash-algorithms',
  'https://staging.bydata.de/api/hub/search/vocabularies/dataset-type',
  'https://staging.bydata.de/api/hub/search/vocabularies/notation-type',
  'https://staging.bydata.de/api/hub/search/vocabularies/documentation-type',
  'https://staging.bydata.de/api/hub/search/vocabularies/dataset-status',
  'https://staging.bydata.de/api/hub/search/vocabularies/licence',
  'https://staging.bydata.de/api/hub/search/vocabularies/political-geocoding-state-key',
  'https://staging.bydata.de/api/hub/search/vocabularies/eurovoc',
  'https://staging.bydata.de/api/hub/search/vocabularies/distribution-type',
  'https://staging.bydata.de/api/hub/search/vocabularies/hvd-category',
  'https://staging.bydata.de/api/hub/search/vocabularies/access-right',
  'https://staging.bydata.de/api/hub/search/vocabularies/political-geocoding-district-key',
  'https://staging.bydata.de/api/hub/search/vocabularies/political-geocoding-regional-key',
  'https://staging.bydata.de/api/hub/search/vocabularies/frequency',
  'https://staging.bydata.de/api/hub/search/vocabularies/planned-availability',
  'https://staging.bydata.de/api/hub/search/vocabularies/file-type',
  'https://staging.bydata.de/api/hub/search/vocabularies/political-geocoding-level',
  'https://staging.bydata.de/api/hub/search/vocabularies/language',
  'https://staging.bydata.de/api/hub/search/vocabularies/data-theme',
  'https://staging.bydata.de/api/hub/search/vocabularies/corporate-body',
  'https://staging.bydata.de/api/hub/search/vocabularies/dataset-types',
  'https://staging.bydata.de/api/hub/search/vocabularies/political-geocoding-municipal-association-key',
  'https://staging.bydata.de/api/hub/search/vocabularies/adms',
  'https://staging.bydata.de/api/hub/search/vocabularies/country',
  'https://staging.bydata.de/api/hub/search/vocabularies/political-geocoding-municipality-key',
  'https://staging.bydata.de/api/hub/search/vocabularies/licenses',
  'https://staging.bydata.de/api/hub/search/vocabularies/spdx-checksum-algorithm',
]

/**
 * Extract vocabulary name from URL
 */
function getVocabularyName(url: string): string {
  return url.split('/').pop() || ''
}

/**
 * Fetch data from a URL and save it to a file
 */
async function fetchAndSaveVocabulary(url: string): Promise<void> {
  const vocabularyName = getVocabularyName(url)
  const targetDir = path.resolve(__dirname, '../src/msw/vocabularies-search')
  const targetPath = path.join(targetDir, `${vocabularyName}.json`)

  try {
    console.log(`Fetching ${vocabularyName} from ${url}`)
    const response = await fetch(url, {
      headers: {
        accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`Failed to fetch ${url}: ${response.status} ${response.statusText}`)
    }

    const data = await response.json()

    await fs.mkdir(targetDir, { recursive: true })

    await fs.writeFile(
      targetPath,
      JSON.stringify(data, null, 2),
      'utf-8',
    )

    console.log(`Saved ${vocabularyName} to ${targetPath}`)
  }
  catch (error) {
    console.error(`Error processing ${vocabularyName}:`, error)
  }
}

/**
 * Main function to fetch all vocabularies
 */
async function fetchAllVocabularies(): Promise<void> {
  console.log(`Starting to fetch ${vucabularyUris.length} vocabularies...`)

  // Process all URLs sequentially to avoid rate limiting
  for (const url of vucabularyUris) {
    await fetchAndSaveVocabulary(url)
  }

  console.log('All vocabularies fetched successfully!')
}

// Execute the main function
fetchAllVocabularies().catch((error) => {
  console.error('Failed to fetch vocabularies:', error)
  process.exit(1)
})
