<?php
/**
 * Feld-Referenz-Generator
 *
 * Erzeugt aus der Feld-Katalog-Datei (config/field-catalog.php) das
 * mehrstufige Markdown-Dokument docs/FELD-REFERENZ.md.
 *
 * Jedes Feld wird in vier Stufen dokumentiert:
 *   1. DCAT-AP-Frage (kurz, standardkonform)
 *   2. Verständliche Frage (kurz, alltagssprachlich)
 *   3. DCAT-AP-Langbeschreibung (vollständig, normkonform)
 *   4. Verständliche Langbeschreibung (nicht-technisch, mit Beispiel)
 *
 * Der Generator hat bewusst KEINE WordPress-Abhängigkeit, damit er sowohl per
 * WP-CLI (`wp open-data-wizard docs`) als auch standalone
 * (`php bin/generate-field-reference.php`, z. B. in der CI) laufen kann.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the multi-tier field reference Markdown from the field catalog.
 *
 * @package OpenDataWizard
 */
class ODW_Field_Reference {

	/**
	 * Human-readable labels for the tier keys used in the catalog.
	 *
	 * @var array<string, string>
	 */
	private const TIER_LABELS = array(
		'mandatory'   => 'Pflicht',
		'recommended' => 'Empfohlen',
		'optional'    => 'Optional',
		'conditional' => 'Bedingt',
	);

	/**
	 * Loads the field catalog array.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function load_catalog(): array {
		/**
		 * Field catalog entries.
		 *
		 * @var array<int, array<string, string>> $catalog
		 */
		$catalog = require __DIR__ . '/../config/field-catalog.php';
		return $catalog;
	}

	/**
	 * Returns a compact catalog map for the admin JS ("Mehr erfahren" panels).
	 *
	 * Only the fields needed on the client are exposed: the distribution meta key
	 * (to locate the DOM field) and the two long descriptions.
	 *
	 * @return array<int, array{meta_key: string, desc_dcat: string, desc_human: string}>
	 */
	public static function js_map(): array {
		$map = array();
		foreach ( self::load_catalog() as $field ) {
			$map[] = array(
				'meta_key'   => (string) $field['meta_key'],
				'desc_dcat'  => (string) $field['desc_dcat'],
				'desc_human' => (string) $field['desc_human'],
			);
		}
		return $map;
	}

	/**
	 * Builds the complete Markdown document.
	 *
	 * @return string
	 */
	public static function build(): string {
		$catalog = self::load_catalog();

		$out   = array();
		$out[] = '# Feld-Referenz — Open Data Wizard';
		$out[] = '';
		$out[] = '> **Automatisch generiert** aus `config/field-catalog.php` durch';
		$out[] = '> `wp open-data-wizard docs` bzw. `php bin/generate-field-reference.php`.';
		$out[] = '> **Nicht von Hand bearbeiten** — Änderungen im Katalog vornehmen und neu generieren.';
		$out[] = '';
		$out[] = 'Diese Referenz dokumentiert jedes Formularfeld des Wizards in **vier Stufen**:';
		$out[] = '';
		$out[] = '1. **DCAT-AP-Frage** — die Frage in der Terminologie des Standards.';
		$out[] = '2. **Verständliche Frage** — dieselbe Frage in Alltagssprache.';
		$out[] = '3. **DCAT-AP-Langbeschreibung** — die vollständige, normkonforme Definition.';
		$out[] = '4. **Verständliche Langbeschreibung** — ausführliche Erklärung ohne Fachjargon, mit Beispiel.';
		$out[] = '';
		$out[] = 'Legende der Stufen-Spalte: **Pflicht** (Veröffentlichung wird ohne dieses Feld blockiert) ·';
		$out[] = '**Empfohlen** · **Optional** · **Bedingt** (nur in bestimmten Fällen relevant).';
		$out[] = '';

		// --- Table of contents, grouped by tab ---
		$out[]       = '## Inhaltsverzeichnis';
		$out[]       = '';
		$current_tab = '';
		foreach ( $catalog as $field ) {
			if ( $field['tab'] !== $current_tab ) {
				$current_tab = $field['tab'];
				$out[]       = '- **' . $current_tab . '**';
			}
			$out[] = '  - [' . $field['q_human'] . '](#' . self::anchor( $field['q_human'] ) . ')';
		}
		$out[] = '';

		// --- Field sections, grouped by tab ---
		$current_tab = '';
		foreach ( $catalog as $field ) {
			if ( $field['tab'] !== $current_tab ) {
				$current_tab = $field['tab'];
				$out[]       = '---';
				$out[]       = '';
				$out[]       = '## ' . $current_tab;
				$out[]       = '';
			}

			$tier  = self::TIER_LABELS[ $field['tier'] ] ?? $field['tier'];
			$vocab = '' !== $field['vocab'] ? '`' . $field['vocab'] . '`' : '—';

			$out[] = '### ' . $field['q_human'];
			$out[] = '';
			$out[] = '| Eigenschaft | Wert |';
			$out[] = '|---|---|';
			$out[] = '| DCAT-Property | `' . $field['dcat_prop'] . '` |';
			$out[] = '| Meta-Key | `' . ( '' !== $field['meta_key'] ? $field['meta_key'] : '—' ) . '` |';
			$out[] = '| Stufe | ' . $tier . ' |';
			$out[] = '| Vokabular | ' . $vocab . ' |';
			$out[] = '';
			$out[] = '**1 · DCAT-AP-Frage:** ' . $field['q_dcat'];
			$out[] = '';
			$out[] = '**2 · Verständliche Frage:** ' . $field['q_human'];
			$out[] = '';
			$out[] = '**3 · DCAT-AP-Langbeschreibung:** ' . $field['desc_dcat'];
			$out[] = '';
			$out[] = '**4 · Verständliche Langbeschreibung:** ' . $field['desc_human'];
			$out[] = '';
		}

		return implode( "\n", $out ) . "\n";
	}

	/**
	 * Writes the generated Markdown to a file.
	 *
	 * @param string $path Absolute target path.
	 * @return int Number of bytes written.
	 */
	public static function write( string $path ): int {
		$bytes = file_put_contents( $path, self::build() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return false === $bytes ? 0 : $bytes;
	}

	/**
	 * Derives a GitHub-style Markdown anchor from a heading string.
	 *
	 * @param string $text Heading text.
	 * @return string
	 */
	private static function anchor( string $text ): string {
		$text = mb_strtolower( $text );
		// Map German umlauts the way GitHub keeps them (as the literal char).
		$text = preg_replace( '/[^\p{L}\p{N}\s-]/u', '', $text ) ?? $text;
		$text = preg_replace( '/\s+/', '-', trim( $text ) ) ?? $text;
		return $text;
	}
}
