<!doctype html>
<!--[if lt IE 10]>
<html class="ie9 no-js" <?php language_attributes(); ?>>
<![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->

<html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->

	<head>
		<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
		<?php if ( is_singular() && pings_open( get_queried_object() ) ) { ?>
		<!-- allow pinned sites -->
		<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>">
		<?php } ?>
		<?php wp_head(); ?>
		<style type="text/css">
	        .grve-bg-green, .grve-bg-hover-green:hover, a.grve-bg-hover-green:hover {
                background-color: #a3b1a2;
                color: #ffffff;
            }
            
            .grve-bg-blue, .grve-bg-hover-blue:hover, a.grve-bg-hover-blue:hover {
                background-color: #a3b1a2;
                color: #ffffff;
            }	    
	
		</style>
	</head>

	<?php
		$grve_theme_layout = 'grve-' . blade_grve_option( 'theme_layout', 'stretched' );
		$grve_header_mode = blade_grve_option( 'header_mode', 'default' );
		$grve_header_fullwidth = blade_grve_option( 'header_fullwidth', '1' );

		$grve_header_data = blade_grve_get_feature_header_data();

		$grve_header_style = $grve_header_data['header_style'];
		$grve_header_overlapping = $grve_header_data['data_overlap'];
		$grve_responsive_header_overlapping = blade_grve_option( 'responsive_header_overlapping', 'no' );
		$grve_header_position = $grve_header_data['data_header_position'];

		$grve_menu_open_type = blade_grve_option( 'header_menu_open_type', 'toggle' );

		//Sticky Header
		$grve_header_sticky_type = blade_grve_option( 'header_sticky_type', 'simple' );
		$grve_disable_sticky = '';
		if ( is_singular() ) {
			$grve_disable_sticky = blade_grve_post_meta( 'grve_disable_sticky', $grve_disable_sticky );
			if ( 'yes' == $grve_disable_sticky  ) {
				$grve_header_sticky_type = 'none';
			} else {
				$grve_header_sticky_type = blade_grve_post_meta( 'grve_sticky_header_type', $grve_header_sticky_type );
			}
		} else if ( blade_grve_is_woo_shop() ) {
			$grve_disable_sticky = blade_grve_post_meta_shop( 'grve_disable_sticky', $grve_disable_sticky );
			if ( 'yes' == $grve_disable_sticky  ) {
				$grve_header_sticky_type = 'none';
			} else {
				$grve_header_sticky_type = blade_grve_post_meta_shop( 'grve_sticky_header_type', $grve_header_sticky_type );
			}
		}
		$grve_header_sticky_type = blade_grve_visibility( 'header_sticky_enabled' ) ? $grve_header_sticky_type : 'none';

		if ( 'default' == $grve_header_mode ) {
			$grve_logo_align = 'left';
			$grve_menu_align = blade_grve_option( 'menu_align', 'right' );
			$grve_menu_type = blade_grve_option( 'menu_type', 'classic' );
			if ( is_singular() ) {
				$grve_menu_type = blade_grve_post_meta( 'grve_menu_type', $grve_menu_type );
			} else if ( blade_grve_is_woo_shop() ) {
				$grve_menu_type = blade_grve_post_meta_shop( 'grve_menu_type', $grve_menu_type );
			}
		} else if ( 'logo-top' == $grve_header_mode ) {
			$grve_logo_align = blade_grve_option( 'header_top_logo_align', 'center' );
			$grve_menu_align = blade_grve_option( 'header_top_menu_align', 'center' );
			$grve_menu_type = blade_grve_option( 'header_top_logo_menu_type', 'classic' );
			if ( is_singular() ) {
				$grve_menu_type = blade_grve_post_meta( 'grve_menu_type', $grve_menu_type );
			} else if ( blade_grve_is_woo_shop() ) {
				$grve_menu_type = blade_grve_post_meta_shop( 'grve_menu_type', $grve_menu_type );
			}
		} else {
			$grve_header_fullwidth = 0;
			$grve_header_overlapping = 'no';
			$grve_header_sticky_type = 'none';
			$grve_menu_align = blade_grve_option( 'header_side_menu_align', 'left' );
			if( 'slide' == $grve_menu_open_type ) {
				$grve_menu_align = 'left';
			}
			$grve_logo_align = blade_grve_option( 'header_side_logo_align', 'left' );
		}

		// Sticky Header Height
		$grve_header_sticky_height = blade_grve_option( 'header_sticky_shrink_height', 60 );
		if( 'logo-top' == $grve_header_mode ){
			$grve_header_sticky_height = intval( blade_grve_option( 'header_sticky_shrink_height', 60 ) + blade_grve_option( 'header_bottom_height', 50 ) );
		}

		//Header Classes
		$grve_header_classes = array();
		if ( 1 == $grve_header_fullwidth ) {
			$grve_header_classes[] = 'grve-fullwidth';
		}
		if ( 'yes' == $grve_header_overlapping ) {
			$grve_header_classes[] = 'grve-overlapping';
		}
		if ( 'yes' == $grve_responsive_header_overlapping ) {
			$grve_header_classes[] = 'grve-responsive-overlapping';
		}
		$grve_header_class_string = implode( ' ', $grve_header_classes );


		//Main Header Classes
		$grve_main_header_classes = array();
		$grve_main_header_classes[] = 'grve-header-' . $grve_header_mode;
		if ( 'side' == $grve_header_mode ) {
			$grve_main_header_classes[] = 'grve-' . $grve_menu_open_type . '-menu';
		} else {
			$grve_main_header_classes[] = 'grve-' . $grve_header_style;
		}
		$grve_header_main_class_string = implode( ' ', $grve_main_header_classes );

		$grve_menu_arrows = blade_grve_option( 'submenu_pointer', 'none' );

		// Main Menu Classes
		$grve_main_menu_classes = array();
		if ( 'side' != $grve_header_mode ) {
			$grve_main_menu_classes[] = 'grve-horizontal-menu';
			$grve_main_menu_classes[] = 'grve-position-' . $grve_menu_align;
			if( 'none' != $grve_menu_arrows ) {
				$grve_main_menu_classes[] = 'grve-' . $grve_menu_arrows;
			}
			if ( 'hidden' != $grve_menu_type ){
				$grve_main_menu_classes[] = 'grve-menu-type-' . $grve_menu_type;
			}
		} else {
			$grve_main_menu_classes[] = 'grve-vertical-menu';
			$grve_main_menu_classes[] = 'grve-align-' . $grve_menu_align;
		}
		$grve_main_menu_class_string = implode( ' ', $grve_main_menu_classes );

		$grve_main_menu = blade_grve_get_header_nav();
		$grve_sidearea_data = blade_grve_get_sidearea_data();


		$grve_header_sticky_devices_enabled = blade_grve_option( 'header_sticky_devices_enabled' );
		$grve_header_sticky_devices = 'no';
		if ( '1' == $grve_header_sticky_devices_enabled ) {
			$grve_header_sticky_devices = 'yes';
		}

	?>

	<body id="grve-body" <?php body_class( $grve_theme_layout ); ?>>
		<style type="text/css">
			.need-to-remove {
			    display: none;
			}
		</style>		
		<?php do_action( 'blade_grve_body_top' ); ?>
		<?php if ( blade_grve_check_theme_loader_visibility() ) { ?>
		<!-- LOADER -->
		<div id="grve-loader-overflow">
			<div class="grve-spinner"></div>
		</div>
		<?php } ?>

		<?php
			// Theme Wrapper Classes
			$grve_theme_wrapper_classes = array();
			if ( 'side' == $grve_header_mode ) {
				$grve_theme_wrapper_classes[] = 'grve-header-side';
			}
			if( 'below' == $grve_header_position && 'yes' == $grve_header_overlapping ) {
				$grve_theme_wrapper_classes[] = 'grve-feature-below';
			}
			$grve_theme_wrapper_class_string = implode( ' ', $grve_theme_wrapper_classes );
		?>

		<!-- Theme Wrapper -->
		<div id="grve-theme-wrapper" class="<?php echo esc_attr( $grve_theme_wrapper_class_string ); ?>">

			<?php
				//Top Bar
				if ( !is_page_template( 'page-templates/template-full-page.php' ) ) {
					blade_grve_print_header_top_bar();
				}
				//FEATURE Header Below
				if( 'below' == $grve_header_position ) {
					blade_grve_print_header_feature();
				}
			?>

			<!-- HEADER -->
			<header id="grve-header" class="<?php echo esc_attr( $grve_header_class_string ); ?>" data-sticky="<?php echo esc_attr( $grve_header_sticky_type ); ?>" data-sticky-height="<?php echo esc_attr( $grve_header_sticky_height ); ?>" data-devices-sticky="<?php echo esc_attr( $grve_header_sticky_devices ); ?>">
				<div class="thanksgiving-top-bar">
					Happy Thanksgiving! Our offices will be closed Thursday and Friday. To avoid shipping delays, please order by 2pm Eastern on Wed., Nov. 21.
				</div>
				<style type="text/css">
					.thanksgiving-top-bar {
						background: #a3b1a2;
						color: #fff;
						display: flex;
						align-items: center;
						justify-content: center;
						position: fixed;
						top: 0;
						left: 0;
						width: 100%;
						height: 40px;
						z-index: 9999;
    					font-family: Montserrat;
					    font-weight: 600;
					    font-size: 13px;
					}
					#grve-header {
						height: 130px;
					}
					#grve-main-header {
						top: 40px;
					}
					#grve-responsive-header > .grve-wrapper {
						top: 40px;
					}
				</style>
				<div class="grve-wrapper clearfix">

					<!-- Header -->
					<div id="grve-main-header" class="<?php echo esc_attr( $grve_header_main_class_string ); ?>">
					<?php
						if ( 'side' == $grve_header_mode ) {
					?>
						<div class="grve-main-header-wrapper clearfix">
							<div class="grve-content">
								<?php do_action( 'blade_grve_side_logo_before' ); ?>
								<?php blade_grve_print_logo( 'side', $grve_logo_align ); ?>
								<?php do_action( 'blade_grve_side_logo_after' ); ?>
								<?php if ( $grve_main_menu != 'disabled' ) { ?>
								<!-- Main Menu -->
								<nav id="grve-main-menu" class="<?php echo esc_attr( $grve_main_menu_class_string ); ?>">
									<div class="grve-wrapper">
										<?php blade_grve_header_nav( $grve_main_menu ); ?>
									</div>
								</nav>
								<!-- End Main Menu -->
								<?php } ?>
							</div>
						</div>
						<div class="grve-header-elements-wrapper grve-align-<?php echo esc_attr( $grve_menu_align); ?>">
							<?php blade_grve_print_header_elements( $grve_sidearea_data ); ?>
						</div>
						<?php blade_grve_print_side_header_bg_image(); ?>
					<?php
						} else if ( 'logo-top' == $grve_header_mode ) {
						//Log on Top Header
					?>
						<div id="grve-top-header">
							<div class="grve-wrapper clearfix">
								<div class="grve-container">
									<?php do_action( 'blade_grve_top_logo_before' ); ?>
									<?php blade_grve_print_logo( 'logo-top', $grve_logo_align ); ?>
									<?php do_action( 'blade_grve_top_logo_after' ); ?>
								</div>
							</div>
						</div>
						<div id="grve-bottom-header">
							<div class="grve-wrapper clearfix">
								<div class="grve-container">
									<div class="grve-header-elements-wrapper grve-position-right">
								<?php
									if ( 'hidden' == $grve_menu_type && 'disabled' != $grve_main_menu ) {
										blade_grve_print_header_hiddenarea_button();
									}
									blade_grve_print_header_elements();
									blade_grve_print_header_sidearea_button( $grve_sidearea_data );
								?>
									</div>
								<?php
									if ( 'hidden' != $grve_menu_type && $grve_main_menu != 'disabled' ) {
								?>
										<!-- Main Menu -->
										<nav id="grve-main-menu" class="<?php echo esc_attr( $grve_main_menu_class_string ); ?>">
											<div class="grve-wrapper">
												<?php blade_grve_header_nav( $grve_main_menu ); ?>
											</div>
										</nav>
										<!-- End Main Menu -->
								<?php
									}
								?>
								</div>
							</div>
						</div>
					<?php
						} else {
						//Default Header
					?>
						<div class="grve-wrapper clearfix">
							<div class="grve-container">
								<?php do_action( 'blade_grve_default_logo_before' ); ?>
								<?php blade_grve_print_logo( 'default', $grve_logo_align ); ?>
								<?php do_action( 'blade_grve_default_logo_after' ); ?>
								<div class="grve-header-elements-wrapper grve-position-right">
							<?php
								if ( 'hidden' == $grve_menu_type && 'disabled' != $grve_main_menu ) {
									blade_grve_print_header_hiddenarea_button();
								}
								blade_grve_print_header_elements();
								blade_grve_print_header_sidearea_button( $grve_sidearea_data );
							?>
								</div>
							<?php
								if ( 'hidden' != $grve_menu_type && $grve_main_menu != 'disabled' ) {
							?>
									<!-- Main Menu -->
									<nav id="grve-main-menu" class="<?php echo esc_attr( $grve_main_menu_class_string ); ?>">
										<div class="grve-wrapper">
											<?php blade_grve_header_nav( $grve_main_menu ); ?>
										</div>
									</nav>
									<!-- End Main Menu -->
							<?php
								}
							?>
							</div>
						</div>
					<?php
						}
					?>
					</div>
					<!-- End Header -->

					<!-- Responsive Header -->
					<div id="grve-responsive-header">
						<div class="grve-wrapper clearfix">
							<div class="grve-container">
							<?php do_action( 'blade_grve_responsive_logo_before' ); ?>
							<?php blade_grve_print_logo( 'responsive' , 'left' ); ?>
							<?php do_action( 'blade_grve_responsive_logo_after' ); ?>
								<div class="grve-header-elements-wrapper grve-position-right">
								<?php do_action( 'blade_grve_responsive_header_elements_first' ); ?>
								<!-- Hidden Menu & Side Area Button -->
								<?php
									if ( 'disabled' != $grve_main_menu || blade_grve_check_header_elements_visibility_responsive() ){
										blade_grve_print_header_hiddenarea_button();
									}
								?>
								<?php blade_grve_print_cart_responsive_link(); ?>
								<?php
									$grve_responsive_sidearea_button = blade_grve_option( 'responsive_sidearea_button_visibility', 'yes');
									if ( 'yes' == $grve_responsive_sidearea_button ) {
										blade_grve_print_header_sidearea_button( $grve_sidearea_data );
									}
								?>
								<!-- End Hidden Menu & Side Area Button -->
								<?php do_action( 'blade_grve_responsive_header_elements_last' ); ?>
								</div>
							</div>
						</div>
					</div>
					<!-- End Responsive Header -->
				</div>
			</header>
			<!-- END HEADER -->

			<?php
				//FEATURE Header Above
				if( 'above' == $grve_header_position ) {
					blade_grve_print_header_feature();
				}

//Omit closing PHP tag to avoid accidental whitespace output errors.
