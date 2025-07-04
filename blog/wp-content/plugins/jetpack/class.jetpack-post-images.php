<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Useful for finding an image to display alongside/in representation of a specific post.
 *
 * @package automattic/jetpack
 */

use Automattic\Block_Delimiter;
use Automattic\Jetpack\Image_CDN\Image_CDN_Core;

/**
 * Useful for finding an image to display alongside/in representation of a specific post.
 *
 * Includes a few different methods, all of which return a similar-format array containing
 * details of any images found. Everything can (should) be called statically, it's just a
 * function-bucket. You can also call Jetpack_PostImages::get_image() to cycle through all of the methods until
 * one of them finds something useful.
 *
 * This file is included verbatim in Jetpack
 */
class Jetpack_PostImages {
	/**
	 * If a slideshow is embedded within a post, then parse out the images involved and return them
	 *
	 * @param int $post_id Post ID.
	 * @param int $width Image width.
	 * @param int $height Image height.
	 * @return array Images.
	 */
	public static function from_slideshow( $post_id, $width = 200, $height = 200 ) {
		$images = array();

		$post = get_post( $post_id );

		if ( ! $post ) {
			return $images;
		}

		if ( ! empty( $post->post_password ) ) {
			return $images;
		}

		if ( false === has_shortcode( $post->post_content, 'slideshow' ) ) {
			return $images; // no slideshow - bail.
		}

		$permalink = get_permalink( $post->ID );

		// Mechanic: Somebody set us up the bomb.
		$old_post                  = $GLOBALS['post'] ?? null;
		$GLOBALS['post']           = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$old_shortcodes            = $GLOBALS['shortcode_tags'];
		$GLOBALS['shortcode_tags'] = array( 'slideshow' => $old_shortcodes['slideshow'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Find all the slideshows.
		preg_match_all( '/' . get_shortcode_regex() . '/sx', $post->post_content, $slideshow_matches, PREG_SET_ORDER );

		ob_start(); // The slideshow shortcode handler calls wp_print_scripts and wp_print_styles... not too happy about that.

		foreach ( $slideshow_matches as $slideshow_match ) {
			$slideshow = do_shortcode_tag( $slideshow_match );
			$pos       = stripos( $slideshow, 'jetpack-slideshow' );
			if ( false === $pos ) { // must be something wrong - or we changed the output format in which case none of the following will work.
				continue;
			}
			$start       = strpos( $slideshow, '[', $pos );
			$end         = strpos( $slideshow, ']', $start );
			$post_images = json_decode( wp_specialchars_decode( str_replace( "'", '"', substr( $slideshow, $start, $end - $start + 1 ) ), ENT_QUOTES ) ); // parse via JSON
			// If the JSON didn't decode don't try and act on it.
			if ( is_array( $post_images ) ) {
				foreach ( $post_images as $post_image ) {
					$post_image_id = absint( $post_image->id );
					if ( ! $post_image_id ) {
						continue;
					}

					$meta = wp_get_attachment_metadata( $post_image_id );

					// Must be larger than 200x200 (or user-specified).
					if ( ! isset( $meta['width'] ) || $meta['width'] < $width ) {
						continue;
					}
					if ( ! isset( $meta['height'] ) || $meta['height'] < $height ) {
						continue;
					}

					$url = wp_get_attachment_url( $post_image_id );

					$images[] = array(
						'type'       => 'image',
						'from'       => 'slideshow',
						'src'        => $url,
						'src_width'  => $meta['width'],
						'src_height' => $meta['height'],
						'href'       => $permalink,
					);
				}
			}
		}
		ob_end_clean();

		// Operator: Main screen turn on.
		$GLOBALS['shortcode_tags'] = $old_shortcodes; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post']           = $old_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $images;
	}

	/**
	 * Filtering out images with broken URL from galleries.
	 *
	 * @param array $galleries Galleries.
	 * @return array $filtered_galleries
	 */
	public static function filter_gallery_urls( $galleries ) {
		$filtered_galleries = array();
		foreach ( $galleries as $this_gallery ) {
			if ( ! isset( $this_gallery['src'] ) ) {
				continue;
			}
			$ids = isset( $this_gallery['ids'] ) ? explode( ',', $this_gallery['ids'] ) : array();
			// Make sure 'src' array isn't associative and has no holes.
			$this_gallery['src'] = array_values( $this_gallery['src'] );
			foreach ( $this_gallery['src'] as $idx => $src_url ) {
				if ( filter_var( $src_url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) === false ) {
					unset( $this_gallery['src'][ $idx ] );
					unset( $ids[ $idx ] );
				}
			}
			if ( isset( $this_gallery['ids'] ) ) {
				$this_gallery['ids'] = implode( ',', $ids );
			}
			// Remove any holes we introduced.
			$this_gallery['src']  = array_values( $this_gallery['src'] );
			$filtered_galleries[] = $this_gallery;
		}

		return $filtered_galleries;
	}

	/**
	 * If a gallery is detected, then get all the images from it.
	 *
	 * @param int $post_id Post ID.
	 * @param int $width Minimum image width to consider.
	 * @param int $height Minimum image height to consider.
	 * @return array Images.
	 */
	public static function from_gallery( $post_id, $width = 200, $height = 200 ) {
		$images = array();

		$post = get_post( $post_id );

		if ( ! $post ) {
			return $images;
		}

		if ( ! empty( $post->post_password ) ) {
			return $images;
		}
		add_filter( 'get_post_galleries', array( __CLASS__, 'filter_gallery_urls' ), 999999 );

		$permalink = get_permalink( $post->ID );

		/**
		 *  Juggle global post object because the gallery shortcode uses the
		 *  global object.
		 *
		 *  See core ticket:
		 *  https://core.trac.wordpress.org/ticket/39304
		 */
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( isset( $GLOBALS['post'] ) ) {
			$juggle_post     = $GLOBALS['post'];
			$GLOBALS['post'] = $post;
			$galleries       = get_post_galleries( $post->ID, false );
			$GLOBALS['post'] = $juggle_post;
		} else {
			$GLOBALS['post'] = $post;
			$galleries       = get_post_galleries( $post->ID, false );
			unset( $GLOBALS['post'] );
		}
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		foreach ( $galleries as $gallery ) {
			if ( ! empty( $gallery['ids'] ) ) {
				$image_ids  = explode( ',', $gallery['ids'] );
				$image_size = isset( $gallery['size'] ) ? $gallery['size'] : 'thumbnail';
				foreach ( $image_ids as $image_id ) {
					$image = wp_get_attachment_image_src( $image_id, $image_size );
					$meta  = wp_get_attachment_metadata( $image_id );

					if ( isset( $gallery['type'] ) && 'slideshow' === $gallery['type'] ) {
						// Must be larger than 200x200 (or user-specified).
						if ( ! isset( $meta['width'] ) || $meta['width'] < $width ) {
							continue;
						}
						if ( ! isset( $meta['height'] ) || $meta['height'] < $height ) {
							continue;
						}
					}

					if ( ! empty( $image[0] ) ) {
						list( $raw_src ) = explode( '?', $image[0] ); // pull off any Query string (?w=250).
						$raw_src         = wp_specialchars_decode( $raw_src ); // rawify it.
						$raw_src         = esc_url_raw( $raw_src ); // clean it.
						$images[]        = array(
							'type'       => 'image',
							'from'       => 'gallery',
							'src'        => $raw_src,
							'src_width'  => $meta['width'] ?? 0,
							'src_height' => $meta['height'] ?? 0,
							'href'       => $permalink,
							'alt_text'   => self::get_alt_text( $image_id ),
						);
					}
				}
			} elseif ( ! empty( $gallery['src'] ) ) {
				foreach ( $gallery['src'] as $src ) {
					list( $raw_src ) = explode( '?', $src ); // pull off any Query string (?w=250).
					$raw_src         = wp_specialchars_decode( $raw_src ); // rawify it.
					$raw_src         = esc_url_raw( $raw_src ); // clean it.
					$images[]        = array(
						'type' => 'image',
						'from' => 'gallery',
						'src'  => $raw_src,
						'href' => $permalink,
					);
				}
			}
		}

		return $images;
	}

	/**
	 * Get attachment images for a specified post and return them. Also make sure
	 * their dimensions are at or above a required minimum.
	 *
	 * @param  int $post_id The post ID to check.
	 * @param  int $width Image width.
	 * @param  int $height Image height.
	 * @return array Containing details of the image, or empty array if none.
	 */
	public static function from_attachment( $post_id, $width = 200, $height = 200 ) {
		$images = array();

		$post = get_post( $post_id );

		if ( ! empty( $post->post_password ) ) {
			return $images;
		}

		$post_images = get_posts(
			array(
				'post_parent'      => $post_id,   // Must be children of post.
				'numberposts'      => 5,          // No more than 5.
				'post_type'        => 'attachment', // Must be attachments.
				'post_mime_type'   => 'image', // Must be images.
				'suppress_filters' => false,
			)
		);

		if ( ! $post_images ) {
			return $images;
		}

		$permalink = get_permalink( $post_id );

		foreach ( $post_images as $post_image ) {
			$current_image = self::get_attachment_data( $post_image->ID, $permalink, $width, $height );
			if ( false !== $current_image ) {
				$images[] = $current_image;
			}
		}

		/*
		* We only want to pass back attached images that were actually inserted.
		* We can load up all the images found in the HTML source and then
		* compare URLs to see if an image is attached AND inserted.
		*/
		$html_images     = self::from_html( $post_id );
		$inserted_images = array();

		foreach ( $html_images as $html_image ) {
			$src = wp_parse_url( $html_image['src'] );
			if ( ! $src || empty( $src['path'] ) ) {
				continue;
			}

			// strip off any query strings from src.
			if ( ! empty( $src['scheme'] ) && ! empty( $src['host'] ) ) {
				$inserted_images[] = $src['scheme'] . '://' . $src['host'] . $src['path'];
			} elseif ( ! empty( $src['host'] ) ) {
				$inserted_images[] = set_url_scheme( 'http://' . $src['host'] . $src['path'] );
			} else {
				$inserted_images[] = site_url( '/' ) . $src['path'];
			}
		}
		foreach ( $images as $i => $image ) {
			if ( ! in_array( $image['src'], $inserted_images, true ) ) {
				unset( $images[ $i ] );
			}
		}

		return $images;
	}

	/**
	 * Check if a Featured Image is set for this post, and return it in a similar
	 * format to the other images?_from_*() methods.
	 *
	 * @param  int $post_id The post ID to check.
	 * @param  int $width Image width.
	 * @param  int $height Image height.
	 * @return array containing details of the Featured Image, or empty array if none.
	 */
	public static function from_thumbnail( $post_id, $width = 200, $height = 200 ) {
		$images = array();

		$post = get_post( $post_id );

		if ( ! empty( $post->post_password ) ) {
			return $images;
		}

		if ( 'attachment' === get_post_type( $post ) && wp_attachment_is_image( $post ) ) {
			$thumb = $post_id;
		} else {
			$thumb = get_post_thumbnail_id( $post );
		}

		if ( $thumb ) {
			$meta = wp_get_attachment_metadata( $thumb );
			// Must be larger than requested minimums.
			if ( ! isset( $meta['width'] ) || $meta['width'] < $width ) {
				return $images;
			}
			if ( ! isset( $meta['height'] ) || $meta['height'] < $height ) {
				return $images;
			}
			$max_dimension = self::get_max_thumbnail_dimension();
			$too_big       = ( ( ! empty( $meta['width'] ) && $meta['width'] > $max_dimension ) || ( ! empty( $meta['height'] ) && $meta['height'] > $max_dimension ) );

			if (
				$too_big &&
				(
					( method_exists( 'Jetpack', 'is_module_active' ) && Jetpack::is_module_active( 'photon' ) ) ||
					( defined( 'IS_WPCOM' ) && IS_WPCOM )
				)
			) {
				$size        = self::determine_thumbnail_size_for_photon( $meta['width'], $meta['height'] );
				$photon_args = array(
					'fit' => $size['width'] . ',' . $size['height'],
				);
				$img_src     = array( Image_CDN_Core::cdn_url( wp_get_attachment_url( $thumb ), $photon_args ), $size['width'], $size['height'], true ); // Match the signature of wp_get_attachment_image_src
			} else {
				$img_src = wp_get_attachment_image_src( $thumb, 'full' );
			}
			if ( ! is_array( $img_src ) ) {
				// If wp_get_attachment_image_src returns false but we know that there should be an image that could be used.
				// we try a bit harder and user the data that we have.
				$thumb_post_data = get_post( $thumb );
				$img_src         = array( $thumb_post_data->guid ?? null, $meta['width'], $meta['height'] );
			}

			// Let's try to use the postmeta if we can, since it seems to be
			// more reliable
			if ( defined( 'IS_WPCOM' ) && IS_WPCOM ) {
				$featured_image = get_post_meta( $post->ID, '_jetpack_featured_image' );
				if ( $featured_image ) {
					$url = $featured_image[0];
				} else {
					$url = $img_src[0];
				}
			} else {
				$url = $img_src[0];
			}
			$images = array(
				array( // Other methods below all return an array of arrays.
					'type'       => 'image',
					'from'       => 'thumbnail',
					'src'        => $url,
					'src_width'  => $img_src[1],
					'src_height' => $img_src[2],
					'href'       => get_permalink( $thumb ),
					'alt_text'   => self::get_alt_text( $thumb ),
				),
			);

		}

		if ( empty( $images ) && ( defined( 'IS_WPCOM' ) && IS_WPCOM ) ) {
			$meta_thumbnail = get_post_meta( $post_id, '_jetpack_post_thumbnail', true );
			if ( ! empty( $meta_thumbnail ) ) {
				if ( ! isset( $meta_thumbnail['width'] ) || $meta_thumbnail['width'] < $width ) {
					return $images;
				}

				if ( ! isset( $meta_thumbnail['height'] ) || $meta_thumbnail['height'] < $height ) {
					return $images;
				}

				$images = array(
					array( // Other methods below all return an array of arrays.
						'type'       => 'image',
						'from'       => 'thumbnail',
						'src'        => $meta_thumbnail['URL'],
						'src_width'  => $meta_thumbnail['width'],
						'src_height' => $meta_thumbnail['height'],
						'href'       => $meta_thumbnail['URL'],
						'alt_text'   => self::get_alt_text( $thumb ),
					),
				);
			}
		}

		return $images;
	}

	/**
	 * Get images from Gutenberg Image blocks.
	 *
	 * @since 6.9.0
	 * @since 14.8 Updated to use Block_Delimiter for improved performance.
	 *
	 * @param mixed $html_or_id The HTML string to parse for images, or a post id.
	 * @param int   $width      Minimum Image width.
	 * @param int   $height     Minimum Image height.
	 */
	public static function from_blocks( $html_or_id, $width = 200, $height = 200 ) {
		$images = array();

		$html_info = self::get_post_html( $html_or_id );

		if ( empty( $html_info['html'] ) ) {
			return $images;
		}

		/*
		 * Use Block_Delimiter to parse our post content HTML,
		 * and find all the block delimiters for supported blocks,
		 * whether they're parent or nested blocks.
		 */
		$supported_blocks = array(
			'core/image',
			'core/media-text',
			'core/gallery',
			'jetpack/tiled-gallery',
			'jetpack/slideshow',
			'jetpack/story',
		);

		foreach ( Block_Delimiter::scan_delimiters( $html_info['html'] ) as $where => $delimiter ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
			// Only process opening delimiters for supported block types.
			if ( Block_Delimiter::OPENER !== $delimiter->get_delimiter_type() ) {
				continue;
			}

			$block_type = $delimiter->allocate_and_return_block_type();
			if ( ! in_array( $block_type, $supported_blocks, true ) ) {
				continue;
			}

			$attributes   = $delimiter->allocate_and_return_parsed_attributes() ?? array();
			$block_images = self::get_images_from_block_attributes( $block_type, $attributes, $html_info, $width, $height );

			if ( ! empty( $block_images ) ) {
				$images = array_merge( $images, $block_images );
			}
		}

		/**
		 * Returning a filtered array because get_attachment_data returns false
		 * for unsuccessful attempts.
		 */
		return array_filter( $images );
	}

	/**
	 * Extract images from block attributes based on block type.
	 *
	 * @since 14.8
	 *
	 * @param string $block_type Block type name.
	 * @param array  $attributes Block attributes.
	 * @param array  $html_info  Info about the post where the block is found.
	 * @param int    $width      Desired image width.
	 * @param int    $height     Desired image height.
	 *
	 * @return array Array of images found.
	 */
	private static function get_images_from_block_attributes( $block_type, $attributes, $html_info, $width, $height ) {
		$images = array();

		switch ( $block_type ) {
			case 'core/image':
			case 'core/media-text':
				$id_key = 'core/image' === $block_type ? 'id' : 'mediaId';
				if ( ! empty( $attributes[ $id_key ] ) ) {
					$image = self::get_attachment_data( $attributes[ $id_key ], $html_info['post_url'], $width, $height );
					if ( false !== $image ) {
						$images[] = $image;
					}
				}
				break;

			case 'core/gallery':
			case 'jetpack/tiled-gallery':
			case 'jetpack/slideshow':
				if ( ! empty( $attributes['ids'] ) && is_array( $attributes['ids'] ) ) {
					foreach ( $attributes['ids'] as $img_id ) {
						$image = self::get_attachment_data( $img_id, $html_info['post_url'], $width, $height );
						if ( false !== $image ) {
							$images[] = $image;
						}
					}
				}
				break;

			case 'jetpack/story':
				if ( ! empty( $attributes['mediaFiles'] ) && is_array( $attributes['mediaFiles'] ) ) {
					foreach ( $attributes['mediaFiles'] as $media_file ) {
						if ( ! empty( $media_file['id'] ) ) {
							$image = self::get_attachment_data( $media_file['id'], $html_info['post_url'], $width, $height );
							if ( false !== $image ) {
								$images[] = $image;
							}
						}
					}
				}
				break;
		}

		return $images;
	}

	/**
	 * Very raw -- just parse the HTML and pull out any/all img tags and return their src
	 *
	 * @param mixed $html_or_id The HTML string to parse for images, or a post id.
	 * @param int   $width      Minimum Image width.
	 * @param int   $height     Minimum Image height.
	 *
	 * @uses DOMDocument
	 *
	 * @return array containing images
	 */
	public static function from_html( $html_or_id, $width = 200, $height = 200 ) {
		$images = array();

		$html_info = self::get_post_html( $html_or_id );

		if ( empty( $html_info['html'] ) ) {
			return $images;
		}

		// Do not go any further if DOMDocument is disabled on the server.
		if ( ! class_exists( 'DOMDocument' ) ) {
			return $images;
		}

		// Let's grab all image tags from the HTML.
		$dom_doc = new DOMDocument();

		// DOMDocument defaults to ISO-8859 because we're loading only the post content, without head tag.
		// Fix: Enforce encoding with meta tag.
		$charset = get_option( 'blog_charset' );
		if ( empty( $charset ) || ! preg_match( '/^[a-zA-Z0-9_-]+$/', $charset ) ) {
			$charset = 'UTF-8';
		}
		$html_prefix = sprintf( '<meta http-equiv="Content-Type" content="text/html; charset=%s">', esc_attr( $charset ) );

		// The @ is not enough to suppress errors when dealing with libxml,
		// we have to tell it directly how we want to handle errors.
		libxml_use_internal_errors( true );
		@$dom_doc->loadHTML( $html_prefix . $html_info['html'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		libxml_use_internal_errors( false );

		$image_tags = $dom_doc->getElementsByTagName( 'img' );

		// For each image Tag, make sure it can be added to the $images array, and add it.
		foreach ( $image_tags as $image_tag ) {
			$img_src = $image_tag->getAttribute( 'src' );

			if ( empty( $img_src ) ) {
				continue;
			}

			// Do not grab smiley images that were automatically created by WP when entering text smilies.
			if ( stripos( $img_src, '/smilies/' ) ) {
				continue;
			}

			// Do not grab Gravatar images.
			if ( stripos( $img_src, 'gravatar.com' ) ) {
				continue;
			}

			// First try to get the width and height from the img attributes, but if they are not set, check to see if they are specified in the url. WordPress automatically names files like foo-1024x768.jpg during the upload process
			$width  = (int) $image_tag->getAttribute( 'width' );
			$height = (int) $image_tag->getAttribute( 'height' );
			if ( 0 === $width && 0 === $height ) {
				preg_match( '/-([0-9]{1,5})x([0-9]{1,5})\.(?:jpg|jpeg|png|gif|webp)$/i', $img_src, $matches );
				if ( ! empty( $matches[1] ) ) {
					$width = (int) $matches[1];
				}
				if ( ! empty( $matches[2] ) ) {
					$height = (int) $matches[2];
				}
			}
			// If width and height are still 0, try to get the id of the image from the class, e.g. wp-image-1234
			if ( 0 === $width && 0 === $height ) {

				preg_match( '/wp-image-([0-9]+)/', $image_tag->getAttribute( 'class' ), $matches );
				if ( ! empty( $matches[1] ) ) {
					$attachment_id = $matches[1];
					$meta          = wp_get_attachment_metadata( $attachment_id );
					$height        = $meta['height'] ?? 0;
					$width         = $meta['width'] ?? 0;
				}
			}

			$meta = array(
				'width'    => $width,
				'height'   => $height,
				'alt_text' => $image_tag->getAttribute( 'alt' ),
			);

			/**
			 * Filters the switch to ignore minimum image size requirements. Can be used
			 * to add custom logic to image dimensions, like only enforcing one of the dimensions,
			 * or disabling it entirely.
			 *
			 * @since 6.4.0
			 *
			 * @param bool $ignore Should the image dimensions be ignored?
			 * @param array $meta Array containing image dimensions parsed from the markup.
			 */
			$ignore_dimensions = apply_filters( 'jetpack_postimages_ignore_minimum_dimensions', false, $meta );

			// Must be larger than 200x200 (or user-specified).
			if (
				! $ignore_dimensions
				&& (
					empty( $meta['width'] )
					|| empty( $meta['height'] )
					|| $meta['width'] < $width
					|| $meta['height'] < $height
				)
			) {
				continue;
			}

			$image = array(
				'type'       => 'image',
				'from'       => 'html',
				'src'        => $img_src,
				'src_width'  => $meta['width'],
				'src_height' => $meta['height'],
				'href'       => $html_info['post_url'],
			);
			if ( ! empty( $meta['alt_text'] ) ) {
				$image['alt_text'] = $meta['alt_text'];
			}
			$images[] = $image;
		}
		return $images;
	}

	/**
	 * Data from blavatar.
	 *
	 * @param    int $post_id The post ID to check.
	 * @param    int $size Size.
	 * @return array containing details of the image, or empty array if none.
	 */
	public static function from_blavatar( $post_id, $size = 96 ) {

		$permalink = get_permalink( $post_id );

		if ( function_exists( 'blavatar_domain' ) && function_exists( 'blavatar_exists' ) && function_exists( 'blavatar_url' ) ) {
			$domain = blavatar_domain( $permalink );

			if ( ! blavatar_exists( $domain ) ) {
				return array();
			}

			$url = blavatar_url( $domain, 'img', $size );
		} else {
			$url = get_site_icon_url( $size );
			if ( ! $url ) {
				return array();
			}
		}

		return array(
			array(
				'type'       => 'image',
				'from'       => 'blavatar',
				'src'        => $url,
				'src_width'  => $size,
				'src_height' => $size,
				'href'       => $permalink,
				'alt_text'   => '',
			),
		);
	}

	/**
	 * Gets a post image from the author avatar.
	 *
	 * @param int    $post_id The post ID to check.
	 * @param int    $size The size of the avatar to get.
	 * @param string $default The default image to use.
	 * @return array containing details of the image, or empty array if none.
	 */
	public static function from_gravatar( $post_id, $size = 96, $default = false ) {
		$post      = get_post( $post_id );
		$permalink = get_permalink( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return array();
		}

		if ( function_exists( 'wpcom_get_avatar_url' ) ) {
			$url = wpcom_get_avatar_url( $post->post_author, $size, $default, true );
			if ( $url && is_array( $url ) ) {
				$url = $url[0];
			}
		} else {
			$url = get_avatar_url(
				$post->post_author,
				array(
					'size'    => $size,
					'default' => $default,
				)
			);
		}

		return array(
			array(
				'type'       => 'image',
				'from'       => 'gravatar',
				'src'        => $url,
				'src_width'  => $size,
				'src_height' => $size,
				'href'       => $permalink,
				'alt_text'   => '',
			),
		);
	}

	/**
	 * Run through the different methods that we have available to try to find a single good
	 * display image for this post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $args Other arguments (currently width and height required for images where possible to determine).
	 * @return array|null containing details of the best image to be used, or null if no image is found.
	 */
	public static function get_image( $post_id, $args = array() ) {
		$image = null;

		/**
		 * Fires before we find a single good image for a specific post.
		 *
		 * @since 2.2.0
		 *
		 * @param int $post_id Post ID.
		 */
		do_action( 'jetpack_postimages_pre_get_image', $post_id );
		$media = self::get_images( $post_id, $args );

		if ( is_array( $media ) ) {
			foreach ( $media as $item ) {
				if ( 'image' === $item['type'] ) {
					$image = $item;
					break;
				}
			}
		}

		/**
		 * Fires after we find a single good image for a specific post.
		 *
		 * @since 2.2.0
		 *
		 * @param int $post_id Post ID.
		 */
		do_action( 'jetpack_postimages_post_get_image', $post_id );

		return $image;
	}

	/**
	 * Get an array containing a collection of possible images for this post, stopping once we hit a method
	 * that returns something useful.
	 *
	 * @param  int   $post_id Post ID.
	 * @param  array $args Optional args, see defaults list for details.
	 * @return array containing images that would be good for representing this post
	 */
	public static function get_images( $post_id, $args = array() ) {
		// Figure out which image to attach to this post.
		$media = array();

		/**
		 * Filters the array of images that would be good for a specific post.
		 * This filter is applied before options ($args) filter the original array.
		 *
		 * @since 2.0.0
		 *
		 * @param array $media Array of images that would be good for a specific post.
		 * @param int $post_id Post ID.
		 * @param array $args Array of options to get images.
		 */
		$media = apply_filters( 'jetpack_images_pre_get_images', $media, $post_id, $args );
		if ( $media ) {
			return $media;
		}

		$defaults = array(
			'width'               => 200, // Required minimum width (if possible to determine).
			'height'              => 200, // Required minimum height (if possible to determine).

			'fallback_to_avatars' => false, // Optionally include Blavatar and Gravatar (in that order) in the image stack.
			'avatar_size'         => 96, // Used for both Grav and Blav.
			'gravatar_default'    => false, // Default image to use if we end up with no Gravatar.

			'from_thumbnail'      => true, // Use these flags to specify which methods to use to find an image.
			'from_slideshow'      => true,
			'from_gallery'        => true,
			'from_attachment'     => true,
			'from_blocks'         => true,
			'from_html'           => true,

			'html_content'        => '', // HTML string to pass to from_html().
		);
		$args     = wp_parse_args( $args, $defaults );

		$media = array();
		if ( $args['from_thumbnail'] ) {
			$media = self::from_thumbnail( $post_id, $args['width'], $args['height'] );
		}
		if ( ! $media && $args['from_slideshow'] ) {
			$media = self::from_slideshow( $post_id, $args['width'], $args['height'] );
		}
		if ( ! $media && $args['from_gallery'] ) {
			$media = self::from_gallery( $post_id );
		}
		if ( ! $media && $args['from_attachment'] ) {
			$media = self::from_attachment( $post_id, $args['width'], $args['height'] );
		}
		if ( ! $media && $args['from_blocks'] ) {
			if ( empty( $args['html_content'] ) ) {
				$media = self::from_blocks( $post_id, $args['width'], $args['height'] ); // Use the post_id, which will load the content.
			} else {
				$media = self::from_blocks( $args['html_content'], $args['width'], $args['height'] ); // If html_content is provided, use that.
			}
		}
		if ( ! $media && $args['from_html'] ) {
			if ( empty( $args['html_content'] ) ) {
				$media = self::from_html( $post_id, $args['width'], $args['height'] ); // Use the post_id, which will load the content.
			} else {
				$media = self::from_html( $args['html_content'], $args['width'], $args['height'] ); // If html_content is provided, use that.
			}
		}

		if ( ! $media && $args['fallback_to_avatars'] ) {
			$media = self::from_blavatar( $post_id, $args['avatar_size'] );
			if ( ! $media ) {
				$media = self::from_gravatar( $post_id, $args['avatar_size'], $args['gravatar_default'] );
			}
		}

		/**
		 * Filters the array of images that would be good for a specific post.
		 * This filter is applied after options ($args) filter the original array.
		 *
		 * @since 2.0.0
		 *
		 * @param array $media Array of images that would be good for a specific post.
		 * @param int $post_id Post ID.
		 * @param array $args Array of options to get images.
		 */
		return apply_filters( 'jetpack_images_get_images', $media, $post_id, $args );
	}

	/**
	 * Takes an image and base pixel dimensions and returns a srcset for the
	 * resized and cropped images, based on a fixed set of multipliers.
	 *
	 * @param  array $image Array containing details of the image.
	 * @param  int   $base_width Base image width (i.e., the width at 1x).
	 * @param  int   $base_height Base image height (i.e., the height at 1x).
	 * @param  bool  $use_widths Whether to generate the srcset with widths instead of multipliers.
	 * @return string The srcset for the image.
	 */
	public static function generate_cropped_srcset( $image, $base_width, $base_height, $use_widths = false ) {
		$srcset = '';

		if ( ! is_array( $image ) || empty( $image['src'] ) || empty( $image['src_width'] ) ) {
			return $srcset;
		}

		$multipliers   = array( 1, 1.5, 2, 3, 4 );
		$srcset_values = array();
		foreach ( $multipliers as $multiplier ) {
			$srcset_width  = (int) ( $base_width * $multiplier );
			$srcset_height = (int) ( $base_height * $multiplier );
			if ( $srcset_width < 1 || $srcset_width > $image['src_width'] ) {
				break;
			}

			$srcset_url = self::fit_image_url(
				$image['src'],
				$srcset_width,
				$srcset_height
			);

			if ( $use_widths ) {
				$srcset_values[] = "{$srcset_url} {$srcset_width}w";
			} else {
				$srcset_values[] = "{$srcset_url} {$multiplier}x";
			}
		}

		if ( count( $srcset_values ) > 1 ) {
			$srcset = implode( ', ', $srcset_values );
		}

		return $srcset;
	}

	/**
	 * Takes an image URL and pixel dimensions then returns a URL for the
	 * resized and cropped image.
	 *
	 * @param  string $src Image URL.
	 * @param  int    $width Image width.
	 * @param  int    $height Image height.
	 * @return string Transformed image URL
	 */
	public static function fit_image_url( $src, $width, $height ) {
		$width  = (int) $width;
		$height = (int) $height;

		if ( $width < 1 || $height < 1 ) {
			return $src;
		}

		// See if we should bypass WordPress.com SaaS resizing.
		if ( has_filter( 'jetpack_images_fit_image_url_override' ) ) {
			/**
			 * Filters the image URL used after dimensions are set by Photon.
			 *
			 * @since 3.3.0
			 *
			 * @param string $src Image URL.
			 * @param int $width Image width.
			 * @param int $width Image height.
			 */
			return apply_filters( 'jetpack_images_fit_image_url_override', $src, $width, $height );
		}

		// If WPCOM hosted image use native transformations.
		$img_host = wp_parse_url( $src, PHP_URL_HOST );
		if ( $img_host && str_ends_with( $img_host, '.files.wordpress.com' ) ) {
			return add_query_arg(
				array(
					'w'    => $width,
					'h'    => $height,
					'crop' => 1,
				),
				set_url_scheme( $src )
			);
		}

		// Use image cdn magic.
		if ( class_exists( Image_CDN_Core::class ) && method_exists( Image_CDN_Core::class, 'cdn_url' ) ) {
			return Image_CDN_Core::cdn_url( $src, array( 'resize' => "$width,$height" ) );
		}

		// Arg... no way to resize image using WordPress.com infrastructure!
		return $src;
	}

	/**
	 * Get HTML from given post content.
	 *
	 * @since 6.9.0
	 *
	 * @param mixed $html_or_id The HTML string to parse for images, or a post id.
	 *
	 * @return array $html_info {
	 * @type string $html     Post content.
	 * @type string $post_url Post URL.
	 * }
	 */
	public static function get_post_html( $html_or_id ) {
		if ( is_numeric( $html_or_id ) ) {
			$post = get_post( $html_or_id );

			if ( empty( $post ) || ! empty( $post->post_password ) ) {
				return '';
			}

			$html_info = array(
				'html'     => $post->post_content, // DO NOT apply the_content filters here, it will cause loops.
				'post_url' => get_permalink( $post->ID ),
			);
		} else {
			$html_info = array(
				'html'     => $html_or_id,
				'post_url' => '',
			);
		}
		return $html_info;
	}

	/**
	 * Get info about a WordPress attachment.
	 *
	 * @since 6.9.0
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $post_url      URL of the post, if we have one.
	 * @param int    $width         Minimum Image width.
	 * @param int    $height        Minimum Image height.
	 * @return array|bool           Image data or false if unavailable.
	 */
	public static function get_attachment_data( $attachment_id, $post_url, $width, $height ) {
		if ( empty( $attachment_id ) ) {
			return false;
		}

		$meta = wp_get_attachment_metadata( $attachment_id );

		if ( empty( $meta ) ) {
			return false;
		}

		if ( ! empty( $meta['videopress'] ) ) {
			// Use poster image for VideoPress videos.
			$url         = $meta['videopress']['poster'];
			$meta_width  = $meta['videopress']['width'];
			$meta_height = $meta['videopress']['height'];
		} elseif ( ! empty( $meta['thumb'] ) ) {
			// On WordPress.com, VideoPress videos have a 'thumb' property with the
			// poster image filename instead.
			$media_url   = wp_get_attachment_url( $attachment_id );
			$url         = str_replace( wp_basename( $media_url ), $meta['thumb'], $media_url );
			$meta_width  = $meta['width'];
			$meta_height = $meta['height'];
		} elseif ( wp_attachment_is( 'video', $attachment_id ) ) {
			// We don't have thumbnail images for non-VideoPress videos - skip them.
			return false;
		} else {
			if ( ! isset( $meta['width'] ) || ! isset( $meta['height'] ) ) {
				return false;
			}
			$url         = wp_get_attachment_url( $attachment_id );
			$meta_width  = $meta['width'];
			$meta_height = $meta['height'];
		}

		if ( $meta_width < $width || $meta_height < $height ) {
			return false;
		}

		return array(
			'type'       => 'image',
			'from'       => 'attachment',
			'src'        => $url,
			'src_width'  => $meta_width,
			'src_height' => $meta_height,
			'href'       => $post_url,
			'alt_text'   => self::get_alt_text( $attachment_id ),
		);
	}

	/**
	 * Get the alt text for an image or other media from the Media Library.
	 *
	 * @since 7.1
	 *
	 * @param int $attachment_id The Post ID of the media.
	 * @return string The alt text value or an empty string.
	 */
	public static function get_alt_text( $attachment_id ) {
		return (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	}

	/**
	 * Determine the size to use with Photon for a thumbnail image.
	 * Images larger than the maximum thumbnail dimension in either dimension are resized to maintain aspect ratio.
	 *
	 * @since 14.6
	 * @see https://github.com/Automattic/jetpack/issues/40349
	 *
	 * @param int $width Original image width.
	 * @param int $height Original image height.
	 * @return array Array containing the width and height to use with Photon (null means auto).
	 */
	public static function determine_thumbnail_size_for_photon( $width, $height ) {
		$max_dimension = self::get_max_thumbnail_dimension();

		// If neither dimension exceeds max size, return original dimensions.
		if ( $width <= $max_dimension && $height <= $max_dimension ) {
			return array(
				'width'  => $width,
				'height' => $height,
			);
		}

		if ( $width >= $height ) {
			// For landscape or square images.
			$dims = image_resize_dimensions( $width, $height, $max_dimension, 0 ); // Height will be calculated automatically.
		} else {
			// For portrait images.
			$dims = image_resize_dimensions( $width, $height, 0, $max_dimension ); // Width will be calculated automatically.
		}

		// $dims can be false if the image is virtually the same size as the max dimension, e.g. wp_fuzzy_number_match.
		if ( $dims && isset( $dims[4] ) && isset( $dims[5] ) ) {
			return array(
				'width'  => $dims[4],
				'height' => $dims[5],
			);
		}

			return array(
				'width'  => $width,
				'height' => $height,
			);
	}

	/**
	 * Function to provide the maximum dimension for a thumbnail image.
	 * Filterable via the `jetpack_post_images_max_dimension` filter.
	 *
	 * @since 14.6
	 * @see https://github.com/Automattic/jetpack/issues/40349
	 *
	 * @return int The maximum dimension for a thumbnail image.
	 */
	public static function get_max_thumbnail_dimension() {
		/**
		 * Filter the maximum dimension allowed for a thumbnail image.
		 * The default value is 1200 pixels.
		 *
		 * @since 14.6
		 *
		 * @param int $max_dimension Maximum dimension in pixels.
		 */
		return (int) apply_filters( 'jetpack_post_images_max_thumbnail_dimension', 1200 );
	}
}
