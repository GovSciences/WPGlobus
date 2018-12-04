<?php
/**
 * File: class-wpglobus-yoast_seo-update-post.php
 *
 * @package WPGlobus\Builders\Yoast_SEO
 * @author  Alex Gor(alexgff)
 */

/**
 * Class WPGlobus_yoast_seo_Update_Post.
 */

if ( ! class_exists( 'WPGlobus_yoast_seo_Update_Post' ) ) :

	// phpcs:ignore PEAR.NamingConventions
	class WPGlobus_yoast_seo_Update_Post {

		/**
		 * Current taxonomy.
		 */
		protected $taxonomy = '';
		
		/**
		 * WP_Term object.
		 */
		protected $tag;
		
		/**
		 * Constructor.
		 */
		public function __construct() {

		
			global $pagenow;
			
			if ( 'edit-tags.php' == $pagenow && $_POST['action'] == 'editedtag' ) {
				/**
				 * Update button was clicked.
				 */
				$this->build_ml_description(); 
			}
		
			/**
			 * @see_file wpglobus\includes\class-wpglobus.php
			 */
			remove_action( 'wp_insert_post_data', array( 'WPGlobus', 'on_save_post_data' ), 10 );

			/**
			 * @todo incorrect the saving post in extra languages with priority = 10
			 */
			add_filter( 'wp_insert_post_data', array( $this, 'filter__wp_insert_post_data' ), 100, 2 );
	
			$this->taxonomy = WPGlobus::Config()->builder->get('taxonomy'); 
			
			if ( $this->taxonomy ) {	
			
				add_filter( 'wp_update_term_data', array( $this, 'filter__update_term_data' ), 10, 4 );

			}
		}
		
		/**
		 * Build multilingual description.
		 * We don't have filter for description like filter for name @see 'wp_update_term_data' in wp-includes\taxonomy.php
		 */
		public function build_ml_description() {
			
			$current_language = WPGlobus::Config()->builder->get_language();
			
			$tag_ID 	= (int) $_POST['tag_ID'];
			$taxonomy 	= $_POST['taxonomy'];
			
			$this->tag = get_term( $tag_ID, $taxonomy );

			$new_desc = array();
			
			foreach ( WPGlobus::Config()->enabled_languages as $lang ) :

				if ( $lang === $current_language ) {

					$text = trim( $_POST['description'] );
					if ( ! empty( $text ) ) {
						$new_desc[ $lang ] = $text;
					}
					
				} else {

					$text = WPGlobus_Core::text_filter( $this->tag->description, $lang, WPGlobus::RETURN_EMPTY );
					if ( ! empty( $text ) ) {
						$new_desc[ $lang ] = $text;
					}
					
				}

			endforeach;		

			$_POST['description'] = WPGlobus_Utils::build_multilingual_string( $new_desc );

		}
		
		/**
		 * Filters term data before it is updated in the database.
		 *
		 * @param array  $data     Term data to be updated.
		 * @param int    $term_id  Term ID.
		 * @param string $taxonomy Taxonomy slug.
		 * @param array  $args     Arguments passed to wp_update_term().
		 */
		public function filter__update_term_data( $data, $term_id, $taxonomy, $args ) {
			
			if ( WPGlobus::Config()->builder && ! WPGlobus::Config()->builder->is_builder_page() ) {
				return $data;
			}
		
			$current_language = WPGlobus::Config()->builder->get_language();
			
			$new_name = array();
			
			foreach ( WPGlobus::Config()->enabled_languages as $lang ) :

				if ( $lang === $current_language ) {

					$text = trim( $data['name'] );
					if ( ! empty( $text ) ) {
						$new_name[ $lang ] = $text;
					}
					
				} else {

					$text = WPGlobus_Core::text_filter( $this->tag->name, $lang, WPGlobus::RETURN_EMPTY );
					if ( ! empty( $text ) ) {
						$new_name[ $lang ] = $text;
					}
					
				}

			endforeach;		

			$data['name'] = WPGlobus_Utils::build_multilingual_string( $new_name );			
		
			return $data;
			
		}
		
		/**
		 * Callback for 'wp_insert_post_data'.
		 *
		 * @param array $data
		 * @param array $postarr
		 *
		 * @return array mixed
		 */
		public function filter__wp_insert_post_data( $data, $postarr ) {

			global $wpdb;
			$_post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d AND post_type = %s LIMIT 1", $postarr['ID'], $postarr['post_type'] ) );

			$fields = array();
			if ( isset( $postarr['post_title'] ) ) {
				$fields['post_title'] = $postarr['post_title'];
			}

			if ( isset( $postarr['post_content'] ) ) {
				$fields['post_content'] = $postarr['post_content'];
			}

			if ( isset( $postarr['post_excerpt'] ) ) {
				$fields['post_excerpt'] = $postarr['post_excerpt'];
			}

			$builder_language = WPGlobus::Config()->builder->get_language();

			if ( empty( $builder_language ) ) {

				if ( empty( $postarr[ WPGlobus::get_language_meta_key() ] ) ) {
					$builder_language = WPGlobus::Config()->default_language;
				} else {
					$builder_language = $postarr[ WPGlobus::get_language_meta_key() ];
				}
			}

			foreach ( $fields as $field => $value ) {

				$tr = array();

				foreach ( WPGlobus::Config()->enabled_languages as $lang ) :

					if ( $lang === $builder_language ) {

						$text = $value;
						if ( WPGlobus_Core::has_translations( $value ) ) {
							$text = WPGlobus_Core::text_filter( $value, $lang, WPGlobus::RETURN_EMPTY );
						}
						if ( ! empty( $text ) ) {
							$tr[ $lang ] = $text;
						}
					} else {
						if ( isset( $_post->$field ) ) {
							$text = WPGlobus_Core::text_filter( $_post->$field, $lang, WPGlobus::RETURN_EMPTY );
							if ( ! empty( $text ) ) {
								$tr[ $lang ] = $text;
							}
						}
					}

				endforeach;

				$data[ $field ] = WPGlobus_Utils::build_multilingual_string( $tr );

			}

			return $data;

		}

	}

endif;