#!/usr/bin/env node
/**
 * SHACL validator for ODW DCAT-AP output.
 *
 * Validates generated fixtures against official DCAT-AP + DCAT-AP.de shapes.
 * Runs offline (no remote ontology loading) with two tiers:
 *
 * - Tier 1: EU + SHACL-DE (~200 shapes)
 * - Tier 2: + german-additions + deprecated + FOAF/vCard ontologies (stricter, default)
 *
 * Usage:
 *   node tests/shacl/validate.mjs [--tier=1|2]
 *
 * Exit codes:
 *   0 — all validations passed (or all violations allowlisted)
 *   1 — violations found
 *
 * @package OpenDataWizard
 */

import { readFileSync, existsSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import { Readable } from 'stream';
import SHACLValidator from 'rdf-validate-shacl';
import $rdf from '@zazuko/env-node';

// Resolve paths.
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = join(__dirname, '../..');

// Parse CLI args.
const args = process.argv.slice(2);
const tierArg = args.find((arg) => arg.startsWith('--tier='));
const tier = tierArg ? parseInt(tierArg.split('=')[1], 10) : 2;

if (![1, 2].includes(tier)) {
	console.error(`Error: Invalid tier "${tier}". Must be 1 or 2.`);
	process.exit(1);
}

// Define tier composition.
const TIERS = {
	1: [
		'config/shacl/dcat-ap-SHACL.ttl',
		'config/shacl/dcat-ap-SHACL-DE.ttl',
	],
	2: [
		'config/shacl/dcat-ap-SHACL.ttl',
		'config/shacl/dcat-ap-SHACL-DE.ttl',
		'config/shacl/dcat-ap-spec-german-additions.ttl',
		'config/shacl/dcat-ap-de-deprecated.ttl',
		'config/shacl/foaf_0_1.rdf',
		'config/shacl/vcard.ttl',
	],
};

// Fixtures to validate.
const FIXTURES = [
	'build/shacl/dataset-minimal.jsonld',
	'build/shacl/dataset-maximal.jsonld',
	'build/shacl/catalog.jsonld',
];

/**
 * Loads an RDF file and returns a dataset (supports Turtle and RDF/XML).
 *
 * @param {string} path - Path to the RDF file.
 * @returns {Promise<import('@rdfjs/types').DatasetCore>}
 */
async function loadRdf(path) {
	const absolutePath = join(projectRoot, path);
	if (!existsSync(absolutePath)) {
		throw new Error(`File not found: ${absolutePath}`);
	}

	// Detect format by file extension
	let contentType;
	if (path.endsWith('.ttl')) {
		contentType = 'text/turtle';
	} else if (path.endsWith('.rdf') || path.endsWith('.xml')) {
		contentType = 'application/rdf+xml';
	} else {
		throw new Error(`Unsupported file format: ${path}`);
	}

	const content = readFileSync(absolutePath, 'utf-8');
	const stream = $rdf.formats.parsers.import(contentType, Readable.from([content]));
	return $rdf.dataset().import(stream);
}

/**
 * Loads a JSON-LD file and returns a dataset.
 *
 * @param {string} path - Path to the JSON-LD file.
 * @returns {Promise<import('@rdfjs/types').DatasetCore>}
 */
async function loadJsonLd(path) {
	const absolutePath = join(projectRoot, path);
	if (!existsSync(absolutePath)) {
		throw new Error(`File not found: ${absolutePath}`);
	}

	const content = readFileSync(absolutePath, 'utf-8');
	const json = JSON.parse(content);
	const stream = $rdf.formats.parsers.import('application/ld+json', Readable.from([JSON.stringify(json)]));
	return $rdf.dataset().import(stream);
}

/**
 * Merges multiple datasets into one.
 *
 * @param {Array<import('@rdfjs/types').DatasetCore>} datasets - Datasets to merge.
 * @returns {import('@rdfjs/types').DatasetCore}
 */
function mergeDatasets(datasets) {
	const merged = $rdf.dataset();
	for (const dataset of datasets) {
		for (const quad of dataset) {
			merged.add(quad);
		}
	}
	return merged;
}

/**
 * Counts sh:NodeShape declarations in the shapes graph.
 *
 * @param {import('@rdfjs/types').DatasetCore} shapesGraph - Shapes graph.
 * @returns {number}
 */
function countNodeShapes(shapesGraph) {
	let count = 0;
	const shNodeShape = $rdf.namedNode('http://www.w3.org/ns/shacl#NodeShape');
	const rdfType = $rdf.namedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#type');

	for (const quad of shapesGraph) {
		if (quad.predicate.equals(rdfType) && quad.object.equals(shNodeShape)) {
			count++;
		}
	}
	return count;
}

/**
 * Loads the allowlist of accepted violations.
 *
 * @returns {Array<object>}
 */
function loadAllowlist() {
	const allowlistPath = join(projectRoot, 'tests/shacl/accepted.json');
	if (!existsSync(allowlistPath)) {
		return [];
	}
	return JSON.parse(readFileSync(allowlistPath, 'utf-8'));
}

/**
 * Checks if a violation is in the allowlist.
 *
 * @param {object} violation - Validation result message.
 * @param {Array<object>} allowlist - Allowlist entries.
 * @returns {boolean}
 */
function isAllowlisted(violation, allowlist) {
	const focusNode = violation.focusNode?.value || '';
	const path = violation.path?.value || '';
	const message = violation.message?.[0]?.value || '';

	return allowlist.some((entry) => {
		const matchesFocus = !entry.focusNode || focusNode.includes(entry.focusNode);
		const matchesPath = !entry.path || path === entry.path;
		const matchesMessage = !entry.messagePattern || message.match(new RegExp(entry.messagePattern));
		return matchesFocus && matchesPath && matchesMessage;
	});
}

/**
 * Validates a data graph against the shapes graph.
 *
 * @param {import('@rdfjs/types').DatasetCore} dataGraph - Data graph to validate.
 * @param {import('@rdfjs/types').DatasetCore} shapesGraph - Shapes graph.
 * @param {string} fixtureName - Name of the fixture being validated.
 * @param {Array<object>} allowlist - Allowlist of accepted violations.
 * @returns {Promise<number>} - Number of non-allowlisted violations.
 */
async function validateFixture(dataGraph, shapesGraph, fixtureName, allowlist) {
	const validator = new SHACLValidator(shapesGraph, { factory: $rdf });
	const report = await validator.validate(dataGraph);

	const violations = [];
	const warnings = [];

	for (const result of report.results) {
		const severity = result.severity?.value || '';
		const violation = {
			focusNode: result.focusNode,
			path: result.path,
			message: result.message,
			severity,
		};

		if (severity.endsWith('#Violation')) {
			violations.push(violation);
		} else if (severity.endsWith('#Warning')) {
			warnings.push(violation);
		}
	}

	// Report warnings (non-fatal).
	if (warnings.length > 0) {
		console.log(`\n⚠️  ${fixtureName}: ${warnings.length} warning(s)\n`);
		for (const warning of warnings) {
			console.log(`  Focus: ${warning.focusNode?.value || 'N/A'}`);
			console.log(`  Path:  ${warning.path?.value || 'N/A'}`);
			console.log(`  ${warning.message?.[0]?.value || 'No message'}\n`);
		}
	}

	// Filter violations by allowlist.
	const nonAllowlisted = violations.filter((v) => !isAllowlisted(v, allowlist));

	if (nonAllowlisted.length > 0) {
		console.error(`\n❌ ${fixtureName}: ${nonAllowlisted.length} violation(s)\n`);
		for (const violation of nonAllowlisted) {
			console.error(`  Focus: ${violation.focusNode?.value || 'N/A'}`);
			console.error(`  Path:  ${violation.path?.value || 'N/A'}`);
			console.error(`  ${violation.message?.[0]?.value || 'No message'}\n`);
		}
	} else if (violations.length > 0) {
		console.log(`\n✓ ${fixtureName}: ${violations.length} violation(s) (all allowlisted)\n`);
	} else {
		console.log(`\n✓ ${fixtureName}: conforms\n`);
	}

	return nonAllowlisted.length;
}

/**
 * Main function.
 */
async function main() {
	console.log(`\nSHACL Validation — Tier ${tier}\n`);
	console.log('Loading shapes...');

	// Load and merge shapes.
	const shapeFiles = TIERS[tier];
	const shapeDatasets = await Promise.all(shapeFiles.map(loadRdf));
	const shapesGraph = mergeDatasets(shapeDatasets);

	// Guard: ensure we have NodeShapes (prevent false green from SHACL-DE-only).
	const shapeCount = countNodeShapes(shapesGraph);
	console.log(`Loaded ${shapeCount} NodeShapes from ${shapeFiles.length} file(s)\n`);

	if (shapeCount === 0) {
		console.error('ERROR: No sh:NodeShape declarations found in shapes graph.');
		console.error('This likely means the shapes were not merged correctly.');
		console.error('Loading SHACL-DE alone produces conforms: true without validation (false green).\n');
		process.exit(1);
	}

	// Load allowlist.
	const allowlist = loadAllowlist();
	if (allowlist.length > 0) {
		console.log(`Loaded ${allowlist.length} allowlisted violation(s)\n`);
	}

	// Validate each fixture.
	let totalViolations = 0;

	for (const fixturePath of FIXTURES) {
		const absolutePath = join(projectRoot, fixturePath);
		if (!existsSync(absolutePath)) {
			console.warn(`⚠️  Fixture not found: ${fixturePath} (skipping)\n`);
			continue;
		}

		console.log(`Validating ${fixturePath}...`);
		const dataGraph = await loadJsonLd(fixturePath);
		const violationCount = await validateFixture(dataGraph, shapesGraph, fixturePath, allowlist);
		totalViolations += violationCount;
	}

	// Exit code.
	if (totalViolations > 0) {
		console.error(`\n❌ Total: ${totalViolations} non-allowlisted violation(s)\n`);
		process.exit(1);
	} else {
		console.log('\n✅ All validations passed\n');
		process.exit(0);
	}
}

main().catch((err) => {
	console.error('FATAL ERROR:', err);
	process.exit(1);
});
