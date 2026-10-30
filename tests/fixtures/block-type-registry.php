<?php
/**
 * Minimaler Ersatz für WP_Block_Type_Registry und WP_Block_Type — nur das,
 * was ODW_Catalog::config_from_content() braucht.
 *
 * @package OpenDataWizard
 */

declare(strict_types=1);

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound

/**
 * Block-Typ mit einer austauschbaren Attribut-Prüfung.
 *
 * @package OpenDataWizard
 */
class WP_Block_Type {

	/**
	 * Prüfung der Attribute.
	 *
	 * @var callable
	 */
	private $prepare;

	/**
	 * Konstruktor.
	 *
	 * @param callable $prepare Prüfung der Attribute.
	 */
	public function __construct( callable $prepare ) {
		$this->prepare = $prepare;
	}

	/**
	 * Wie WP_Block_Type::prepare_attributes_for_render().
	 *
	 * @param array<string, mixed> $attributes Attribute.
	 * @return array<string, mixed>
	 */
	public function prepare_attributes_for_render( $attributes ) {
		return call_user_func( $this->prepare, $attributes );
	}
}

/**
 * Registry mit genau einem registrierten Typ (oder keinem).
 *
 * @package OpenDataWizard
 */
class WP_Block_Type_Registry {

	/**
	 * Registrierter Typ, null: keiner.
	 *
	 * @var WP_Block_Type|null
	 */
	public static $type = null;

	/**
	 * Wie WP_Block_Type_Registry::get_instance().
	 *
	 * @return self
	 */
	public static function get_instance() {
		return new self();
	}

	/**
	 * Wie WP_Block_Type_Registry::get_registered().
	 *
	 * @param string $name Blockname.
	 * @return WP_Block_Type|null
	 */
	public function get_registered( $name ) {
		unset( $name );
		return self::$type;
	}
}
