<?php
/**
 * Minimaler JSON-LD → Turtle Serializer.
 *
 * Wandelt die vom Plugin erzeugte, formbekannte DCAT-AP.de-JSON-LD-Struktur in
 * Turtle (`text/turtle`) um — ohne externe RDF-Bibliothek. Deckt genau die
 * Konstrukte ab, die `odw_build_dataset_jsonld()` und der Katalog-Builder
 * verwenden:
 *   - Knoten mit `@id`            → benannte Subjekte / IRI-Referenzen
 *   - Knoten ohne `@id`           → Blank Nodes (`[ … ]`)
 *   - `{ @value, @language }`     → `"…"@de`
 *   - `{ @value, @type }`         → `"…"^^xsd:date`
 *   - `{ @id }`                   → `<IRI>`
 *   - Listen                      → `obj1, obj2`
 *   - `@type` (CURIE)             → `a dcat:Dataset`
 *
 * Die Prefix-Deklarationen stammen aus dem `@context` des übergebenen Dokuments.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serialises the plugin's DCAT-AP.de JSON-LD documents to Turtle.
 *
 * @package OpenDataWizard
 */
class ODW_Rdf {

	/** Keys that carry JSON-LD semantics rather than RDF predicates. */
	private const SPECIAL_KEYS = array( '@id', '@type', '@context', '@value', '@language' );

	/**
	 * Serialise a JSON-LD document (as produced by the catalog builder) to Turtle.
	 *
	 * @param array<string, mixed> $doc JSON-LD document; `@context` must map prefixes to IRIs.
	 * @return string Turtle serialisation.
	 */
	public static function to_turtle( array $doc ): string {
		$context = isset( $doc['@context'] ) && is_array( $doc['@context'] ) ? $doc['@context'] : array();
		$root    = $doc;
		unset( $root['@context'] );

		// Named subjects to render at the top level, discovered while rendering.
		$queue = array();
		$seen  = array();

		self::enqueue_subject( $root, $queue, $seen );

		$blocks = array();
		// Nested nodes with an @id, discovered while rendering, are appended to the
		// queue and rendered as their own subjects (FIFO keeps a stable order).
		while ( ! empty( $queue ) ) {
			$node     = array_shift( $queue );
			$blocks[] = self::render_subject( $node, $queue, $seen );
		}

		return self::render_prefixes( $context ) . "\n" . implode( "\n\n", $blocks ) . "\n";
	}

	/**
	 * Emit `@prefix` lines for every prefix in the context.
	 *
	 * @param array<string, mixed> $context Prefix => IRI map.
	 * @return string
	 */
	private static function render_prefixes( array $context ): string {
		$lines = array();
		foreach ( $context as $prefix => $iri ) {
			if ( is_string( $iri ) ) {
				$lines[] = '@prefix ' . $prefix . ': <' . self::escape_iri( $iri ) . '> .';
			}
		}
		sort( $lines );
		return implode( "\n", $lines ) . "\n";
	}

	/**
	 * Register a node with an `@id` (or the root) as a top-level subject.
	 *
	 * @param array<string, mixed> $node     Node to enqueue.
	 * @param array<int, array>    $subjects Subject list (by reference).
	 * @param array<string, bool>  $seen     Deduplication set of @id values (by reference).
	 */
	private static function enqueue_subject( array $node, array &$subjects, array &$seen ): void {
		$id = isset( $node['@id'] ) ? (string) $node['@id'] : '';
		if ( '' !== $id ) {
			if ( isset( $seen[ $id ] ) ) {
				return;
			}
			$seen[ $id ] = true;
		}
		$subjects[] = $node;
	}

	/**
	 * Render one subject block: `<id> a Type ; pred obj ; … .`
	 *
	 * @param array<string, mixed> $node     Subject node.
	 * @param array<int, array>    $subjects Subject list (by reference; may grow).
	 * @param array<string, bool>  $seen     Deduplication set (by reference).
	 * @return string
	 */
	private static function render_subject( array $node, array &$subjects, array &$seen ): string {
		$id      = isset( $node['@id'] ) ? (string) $node['@id'] : '';
		$subject = '' !== $id ? '<' . self::escape_iri( $id ) . '>' : '[]';

		$predicates = self::render_predicates( $node, $subjects, $seen );

		if ( empty( $predicates ) ) {
			$types = self::render_types( $node );
			return $subject . ' a ' . ( '' !== $types ? $types : 'rdfs:Resource' ) . ' .';
		}

		return $subject . ' ' . implode( " ;\n    ", $predicates ) . ' .';
	}

	/**
	 * Build the predicate list (`a Type`, then each RDF predicate) for a node.
	 *
	 * @param array<string, mixed> $node     Node.
	 * @param array<int, array>    $subjects Subject list (by reference).
	 * @param array<string, bool>  $seen     Deduplication set (by reference).
	 * @return array<int, string>
	 */
	private static function render_predicates( array $node, array &$subjects, array &$seen ): array {
		$parts = array();

		$types = self::render_types( $node );
		if ( '' !== $types ) {
			$parts[] = 'a ' . $types;
		}

		foreach ( $node as $key => $value ) {
			if ( in_array( $key, self::SPECIAL_KEYS, true ) ) {
				continue;
			}

			$object = self::render_object( $value, $subjects, $seen );

			// Eine leere Liste (z. B. ein Katalog ohne veröffentlichte Datensätze)
			// liefert keinen Objektterm. Das Prädikat muss dann ganz entfallen —
			// sonst entstünde "dcat:dataset ." und damit ungültiges Turtle.
			if ( '' === $object ) {
				continue;
			}

			$parts[] = $key . ' ' . $object;
		}

		return $parts;
	}

	/**
	 * Render the `@type` value(s) as a comma-separated CURIE/IRI list.
	 *
	 * @param array<string, mixed> $node Node.
	 * @return string Empty string when no @type.
	 */
	private static function render_types( array $node ): string {
		if ( ! isset( $node['@type'] ) ) {
			return '';
		}
		$types = is_array( $node['@type'] ) ? $node['@type'] : array( $node['@type'] );
		$out   = array();
		foreach ( $types as $type ) {
			$out[] = self::term( (string) $type );
		}
		return implode( ', ', $out );
	}

	/**
	 * Render an object value (scalar, list, language/typed literal, IRI ref or blank node).
	 *
	 * @param mixed               $value    Object value.
	 * @param array<int, array>   $subjects Subject list (by reference).
	 * @param array<string, bool> $seen     Deduplication set (by reference).
	 * @return string
	 */
	private static function render_object( $value, array &$subjects, array &$seen ): string {
		if ( is_array( $value ) && array_is_list( $value ) ) {
			$items = array();
			foreach ( $value as $item ) {
				$items[] = self::render_object( $item, $subjects, $seen );
			}
			return implode( ', ', $items );
		}

		if ( is_array( $value ) ) {
			// Typed / language literal.
			if ( array_key_exists( '@value', $value ) ) {
				$lit = self::quote( (string) $value['@value'] );
				if ( isset( $value['@language'] ) && '' !== (string) $value['@language'] ) {
					return $lit . '@' . (string) $value['@language'];
				}
				if ( isset( $value['@type'] ) && '' !== (string) $value['@type'] ) {
					return $lit . '^^' . self::term( (string) $value['@type'] );
				}
				return $lit;
			}

			// Node with an @id: reference it; if it carries further properties,
			// enqueue it for its own subject block.
			if ( isset( $value['@id'] ) && '' !== (string) $value['@id'] ) {
				if ( self::has_properties( $value ) ) {
					self::enqueue_subject( $value, $subjects, $seen );
				}
				return '<' . self::escape_iri( (string) $value['@id'] ) . '>';
			}

			// Blank node.
			return self::render_blank( $value, $subjects, $seen );
		}

		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		if ( is_int( $value ) || is_float( $value ) ) {
			return (string) $value;
		}

		return self::quote( (string) $value );
	}

	/**
	 * Render a node without an @id as an inline blank node `[ … ]`.
	 *
	 * @param array<string, mixed> $node     Node.
	 * @param array<int, array>    $subjects Subject list (by reference).
	 * @param array<string, bool>  $seen     Deduplication set (by reference).
	 * @return string
	 */
	private static function render_blank( array $node, array &$subjects, array &$seen ): string {
		$parts = self::render_predicates( $node, $subjects, $seen );
		if ( empty( $parts ) ) {
			return '[]';
		}
		return '[ ' . implode( ' ; ', $parts ) . ' ]';
	}

	/**
	 * True when the node has RDF predicates beyond @id/@type.
	 *
	 * @param array<string, mixed> $node Node.
	 * @return bool
	 */
	private static function has_properties( array $node ): bool {
		foreach ( $node as $key => $unused ) {
			if ( ! in_array( $key, self::SPECIAL_KEYS, true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Render a type/predicate term: a CURIE (`dcat:Dataset`) stays as-is, a full
	 * IRI becomes `<IRI>`.
	 *
	 * @param string $term Term.
	 * @return string
	 */
	private static function term( string $term ): string {
		if ( preg_match( '#^https?://#', $term ) ) {
			return '<' . self::escape_iri( $term ) . '>';
		}
		return $term;
	}

	/**
	 * Escape an IRI for use inside a Turtle `<…>` reference.
	 *
	 * @param string $iri IRI.
	 * @return string
	 */
	private static function escape_iri( string $iri ): string {
		// Turtle IRIREF forbids spaces, <, >, ", {, }, |, ^, `, \ and control chars.
		$iri = preg_replace( '/[\x00-\x20<>"{}|^`\\\\]/', '', $iri );
		return (string) $iri;
	}

	/**
	 * Quote a string literal per Turtle rules.
	 *
	 * @param string $value Literal value.
	 * @return string
	 */
	private static function quote( string $value ): string {
		$value = str_replace(
			array( '\\', '"', "\n", "\r", "\t" ),
			array( '\\\\', '\\"', '\\n', '\\r', '\\t' ),
			$value
		);
		return '"' . $value . '"';
	}
}
