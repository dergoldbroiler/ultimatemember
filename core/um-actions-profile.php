<?php

	/***
	***	@um_profile_content_{main_tab}
	***/
	add_action('um_profile_content_main','um_profile_content_main');
	function um_profile_content_main( $args ) {
		
		extract( $args );
	
		do_action("um_before_form", $args);
				
		do_action("um_before_{$template}_fields", $args);
				
		do_action("um_main_{$template}_fields", $args);
				
		do_action("um_after_form_fields", $args);
				
		do_action("um_after_{$template}_fields", $args);
				
		do_action("um_after_form", $args);
	
	}
	
	/***
	***	@update user's profile
	***/
	add_action('um_user_edit_profile', 'um_user_edit_profile', 10);
	function um_user_edit_profile($args){
		
		global $ultimatemember;
		
		$to_update = null;
		$files = null;
		
		if ( isset( $args['user_id'] ) ) {
			if ( um_current_user_can('edit', $args['user_id'] ) ) {
				$ultimatemember->user->set( $args['user_id'] );
			} else {
				wp_die( __('You are not allowed to edit this user.','ultimatemember') );
			}
		} else if ( isset( $args['_user_id'] ) ) {
			$ultimatemember->user->set( $args['_user_id'] );
		}
		
		$userinfo = $ultimatemember->user->profile;
		
		$fields = unserialize( $args['custom_fields'] );
		
		do_action('um_user_before_updating_profile', $userinfo );
		
		// loop through fields
		foreach( $fields as $key => $array ) {
		
			if ( $fields[$key]['type'] == 'multiselect' ||  $fields[$key]['type'] == 'checkbox' && !isset($args['submitted'][$key]) ) {
				delete_user_meta( um_user('ID'), $key );
			}
			
			if ( isset( $args['submitted'][ $key ] ) ) {

				if ( isset( $fields[$key]['type'] ) && in_array( $fields[$key]['type'], array('image','file') ) && um_is_temp_upload( $args['submitted'][ $key ] )  ) {
					
					$files[ $key ] = $args['submitted'][ $key ];
				
				} else {

					if ( isset( $userinfo[$key]) && $args['submitted'][$key] != $userinfo[$key] ) {
						$to_update[ $key ] = $args['submitted'][ $key ];
					} else if ( $args['submitted'][$key] ) {
						$to_update[ $key ] = $args['submitted'][ $key ];
					}
				
				}
				
			}
		}
		
		if ( isset( $args['submitted']['description'] ) ) {
			$to_update['description'] = $ultimatemember->validation->remove_html( $args['submitted']['description'] );
		}

		if ( is_array( $to_update ) ) {
			$ultimatemember->user->update_profile( $to_update );
		}

		if ( is_array( $files ) ) {
			$ultimatemember->user->update_files( $files );
		}
		
		do_action('um_user_after_updating_profile', $to_update );
		
		if ( !isset( $args['is_signup'] ) ) {
			exit( wp_redirect( um_edit_my_profile_cancel_uri() ) );
		}
		
	}
	
	/***
	***	@if editing another user
	***/
	add_action('um_after_form_fields', 'um_editing_user_id_input');
	function um_editing_user_id_input($args){ 
		global $ultimatemember;
		if ( $ultimatemember->fields->editing == 1 && $ultimatemember->fields->set_mode == 'profile' && $ultimatemember->user->target_id ) { ?>
	
		<input type="hidden" name="user_id" id="user_id" value="<?php echo $ultimatemember->user->target_id; ?>" />

		<?php
		
		}
	}
	
	/***
	***	@meta description
	***/
	add_action('wp_head', 'um_profile_dynamic_meta_desc', 9999999);
	function um_profile_dynamic_meta_desc() {
	
		global $ultimatemember;
		
		if ( um_is_core_page('user') && um_get_requested_user() ) {
		
			um_fetch_user( um_get_requested_user() );
			
			$content = $ultimatemember->mail->convert_tags( um_get_option('profile_desc') );
			
			um_reset_user();
			
			?>
		
			<meta name="description" content="<?php echo $content; ?>">
		
			<?php
		
		}
	}
	
	/***
	***	@profile header cover
	***/
	add_action('um_profile_header_cover_area', 'um_profile_header_cover_area' );
	function um_profile_header_cover_area( $args ) {
		global $ultimatemember;
		
		if ( $args['cover_enabled'] == 1 ) {
			
			$default_cover = um_get_option('default_cover');
			
			$overlay = '<span class="um-cover-overlay">
				<span class="um-cover-overlay-s">
					<ins>
						<i class="um-faicon-picture-o"></i>
						<span class="um-cover-overlay-t">'.__('Change your cover photo').'</span>
					</ins>
				</span>
			</span>';
			
		?>

			<div class="um-cover <?php if ( um_profile('cover_photo') || ( $default_cover && $default_cover['url'] ) ) echo 'has-cover'; ?>" data-user_id="<?php echo um_profile_id(); ?>" data-ratio="<?php echo $args['cover_ratio']; ?>">
			
				<?php
				
					if ( $ultimatemember->fields->editing ) {
					
						$items = array(
									'<a href="#" class="um-manual-trigger" data-parent=".um-cover" data-child=".um-btn-auto-width">'.__('Change cover photo','ultimatemember').'</a>',
									'<a href="#" class="um-reset-cover-photo" data-user_id="'.um_profile_id().'">'.__('Remove','ultimatemember').'</a>',
									'<a href="#" class="um-dropdown-hide">'.__('Cancel','ultimatemember').'</a>',
						);
						
						echo $ultimatemember->menu->new_ui( 'bc', 'div.um-cover', 'click', $items );
						
					}
				?>
				
				<?php $ultimatemember->fields->add_hidden_field( 'cover_photo' ); ?>
				
				<?php echo $overlay; ?>
				
				<div class="um-cover-e">
				
					<?php if ( um_profile('cover_photo') ) { ?>
						
					<?php
					
					if( $ultimatemember->mobile->isMobile() ){
						if ( $ultimatemember->mobile->isTablet() ) {
							echo um_user('cover_photo', 1000);
						} else {
							echo um_user('cover_photo', 300);
						}
					} else {
						echo um_user('cover_photo', 1000);
					}
					
					?>
						
					<?php } elseif ( $default_cover && $default_cover['url'] ) {

						$default_cover = $default_cover['url'];
						
						echo '<img src="'. $default_cover . '" alt="" />';
						
					} else {
					
						if ( !isset( $ultimatemember->user->cannot_edit ) ) { ?>
					
						<a href="#" class="um-cover-add um-manual-trigger" data-parent=".um-cover" data-child=".um-btn-auto-width"><span class="um-cover-add-i"><i class="um-icon-plus um-tip-n" title="<?php _e('Upload a cover photo','ultimatemember'); ?>"></i></span></a>
					
					<?php }
					
					} ?>
					
				</div>
				
			</div>
			
			<?php

		}
		
	}
	
	/***
	***	@profile header
	***/
	add_action('um_profile_header', 'um_profile_header' );
	function um_profile_header( $args ) {
		global $ultimatemember;
		
		$classes = null;
		
		if ( !$args['cover_enabled'] ) {
			$classes .= ' no-cover';
		}
		
		$default_size = str_replace( 'px', '', $args['photosize'] );
		
		$overlay = '<span class="um-profile-photo-overlay">
			<span class="um-profile-photo-overlay-s">
				<ins>
					<i class="um-faicon-camera"></i>
				</ins>
			</span>
		</span>';
		
		?>
		
			<div class="um-header<?php echo $classes; ?>">
			
				<?php do_action('um_pre_header_editprofile', $args); ?>
				
				<div class="um-profile-photo" data-user_id="<?php echo um_profile_id(); ?>">

					<a href="<?php echo um_user_profile_url(); ?>" class="um-profile-photo-img" title="<?php echo um_user('display_name'); ?>"><?php echo $overlay . get_avatar( um_user('ID'), $default_size ); ?></a>
					
					<?php
					
					if ( !isset( $ultimatemember->user->cannot_edit ) ) { 
					
						$ultimatemember->fields->add_hidden_field( 'profile_photo' );
						
						if ( !um_profile('profile_photo') ) { // has profile photo
						
							$items = array(
								'<a href="#" class="um-manual-trigger" data-parent=".um-profile-photo" data-child=".um-btn-auto-width">'.__('Upload photo','ultimatemember').'</a>',
								'<a href="#" class="um-dropdown-hide">'.__('Cancel','ultimatemember').'</a>',
							);
							
							echo $ultimatemember->menu->new_ui( 'bc', 'div.um-profile-photo', 'click', $items );
							
						} else if ( $ultimatemember->fields->editing == true ) {
						
							$items = array(
								'<a href="#" class="um-manual-trigger" data-parent=".um-profile-photo" data-child=".um-btn-auto-width">'.__('Change photo','ultimatemember').'</a>',
								'<a href="#" class="um-reset-profile-photo" data-user_id="'.um_profile_id().'" data-default_src="'.um_get_default_avatar_uri().'">'.__('Remove photo','ultimatemember').'</a>',
								'<a href="#" class="um-dropdown-hide">'.__('Cancel','ultimatemember').'</a>',
							);
							
							echo $ultimatemember->menu->new_ui( 'bc', 'div.um-profile-photo', 'click', $items );
							
						}
					
					}
					
					?>
					
				</div>
				
				<div class="um-profile-meta">
				
					<div class="um-main-meta">
						<?php if ( $args['show_name'] ) { ?>
						<div class="um-name"><a href="<?php echo um_user_profile_url(); ?>" title="<?php echo um_user('display_name'); ?>"><?php echo um_user('display_name'); ?></a></div>
						<?php } ?>
						<div class="um-clear"></div>
					</div>
					
					<?php if ( isset( $args['metafields'] ) && !empty( $args['metafields'] ) ) { ?>
					<div class="um-meta">
						
						<?php echo $ultimatemember->profile->show_meta( $args['metafields'] ); ?>
							
					</div>
					<?php } ?>

					<?php if ( $ultimatemember->fields->viewing == true && um_user('description') && $args['show_bio'] ) { ?>
					
					<div class="um-meta-text"><?php echo um_user('description'); ?></div>
					
					<?php } else if ( $ultimatemember->fields->editing == true  && $args['show_bio'] ) { ?>
					
					<div class="um-meta-text">
						<textarea placeholder="<?php _e('Tell us a bit about yourself...','ultimatemember'); ?>" name="<?php echo 'description-' . $args['form_id']; ?>" id="<?php echo 'description-' . $args['form_id']; ?>"><?php if ( um_user('description') ) { echo um_user('description'); } ?></textarea>
						
						<?php if ( $ultimatemember->fields->is_error('description') )
							echo $ultimatemember->fields->field_error( $ultimatemember->fields->show_error('description') ); ?>
						
					</div>
					
					<?php } ?>
					
					<div class="um-profile-status <?php echo um_user('account_status'); ?>">
						<span><?php printf(__('This user account status is %s','ultimatemember'), um_user('account_status_name') ); ?></span>
					</div>
					
				</div><div class="um-clear"></div>
				
			</div>
			
		<?php
	}
	
	/***
	***	@adds profile permissions to view/edit
	***/
	add_action('um_pre_profile_shortcode', 'um_pre_profile_shortcode');
	function um_pre_profile_shortcode($args){
		global $ultimatemember;
		extract( $args );

		if ( $mode == 'profile' && $ultimatemember->fields->editing == false ) {
			$ultimatemember->fields->viewing = 1;
			
			if ( um_get_requested_user() ) {
				if ( !um_can_view_profile( um_get_requested_user() ) ) um_redirect_home();
				if ( !um_current_user_can('edit', um_get_requested_user() ) ) $ultimatemember->user->cannot_edit = 1;
				um_fetch_user( um_get_requested_user() );
			} else {
				if ( !is_user_logged_in() ) um_redirect_home();
				if ( !um_user('can_edit_profile') ) $ultimatemember->user->cannot_edit = 1;
			}
			
		}

		if ( $mode == 'profile' && $ultimatemember->fields->editing == true ) {
			$ultimatemember->fields->editing = 1;
		
			if ( um_get_requested_user() ) {
				if ( !um_current_user_can('edit', um_get_requested_user() ) ) um_redirect_home();
				um_fetch_user( um_get_requested_user() );
			}
			
		}
		
	}
	
	/***
	***	@display the edit profile icon
	***/
	add_action('um_pre_header_editprofile', 'um_add_edit_icon' );
	function um_add_edit_icon( $args ) {
		global $ultimatemember;
		$output = '';
		
		if ( !is_user_logged_in() ) return; // not allowed for guests
		
		if ( isset( $ultimatemember->user->cannot_edit ) && $ultimatemember->user->cannot_edit == 1 ) return; // do not proceed if user cannot edit
		
		if ( $ultimatemember->fields->editing == true ) {
		
		?>
			
		<div class="um-profile-edit um-profile-headericon">
		
			<a href="#" class="um-profile-edit-a um-profile-save"><i class="um-faicon-check"></i></a>
		
		</div>
		
		<?php } else { ?>
		
		<div class="um-profile-edit um-profile-headericon">
		
			<a href="#" class="um-profile-edit-a"><i class="um-faicon-cog"></i></a>
		
			<?php
			
			$items = array(
				'editprofile' => '<a href="'.um_edit_my_profile_uri().'" class="real_url">'.__('Edit Profile','ultimatemember').'</a>',
				'myaccount' => '<a href="'.um_get_core_page('account').'" class="real_url">'.__('My Account','ultimatemember').'</a>',
				'logout' => '<a href="'.um_get_core_page('logout').'" class="real_url">'.__('Logout','ultimatemember').'</a>',
				'cancel' => '<a href="#" class="um-dropdown-hide">'.__('Cancel','ultimatemember').'</a>',
			);
			
			$cancel = $items['cancel'];
				
			if ( !um_is_myprofile() ) {
				
				$actions = $ultimatemember->user->get_admin_actions();
				
				unset( $items['myaccount'] );
				unset( $items['logout'] );
				unset( $items['cancel'] );
				
				if ( is_array( $actions ) ) {
				$items = array_merge( $items, $actions );
				}
				
				$items['cancel'] = $cancel;
				
				$items = apply_filters('um_profile_edit_menu_items', $items );
				
			} else {
			
				$items = apply_filters('um_myprofile_edit_menu_items', $items );
				
			}
			
			echo $ultimatemember->menu->new_ui( $args['header_menu'], 'div.um-profile-edit', 'click', $items );
			
			?>
		
		</div>
		
		<?php
		}
		
	}
	
	/***
	***	@Show Fields
	***/
	add_action('um_main_profile_fields', 'um_add_profile_fields', 100);
	function um_add_profile_fields($args){
		global $ultimatemember;
		
		if ( $ultimatemember->fields->editing == true ) {
		
			echo $ultimatemember->fields->display( 'profile', $args );
			
		} else {
		
			$ultimatemember->fields->viewing = true;
			
			echo $ultimatemember->fields->display_view( 'profile', $args );
			
		}
		
	}
	
	/***
	***	@form processing
	***/
	add_action('um_submit_form_profile', 'um_submit_form_profile', 10);
	function um_submit_form_profile($args){
		global $ultimatemember;
	
		if ( !isset($ultimatemember->form->errors) ) do_action('um_user_edit_profile', $args);

		do_action('um_user_profile_extra_hook', $args );
		
	}
	
	/***
	***	@Show the submit button (highest priority)
	***/
	add_action('um_after_profile_fields', 'um_add_submit_button_to_profile', 1000);
	function um_add_submit_button_to_profile($args){
		global $ultimatemember;
		
		// DO NOT add when reviewing user's details
		if ( $ultimatemember->user->preview == true && is_admin() ) return;
		
		// only when editing
		if ( $ultimatemember->fields->editing == false ) return;
		
		?>
		
		<div class="um-col-alt">
		
			<?php if ( isset($args['secondary_btn']) && $args['secondary_btn'] != 0 ) { ?>
			
			<div class="um-left um-half"><input type="submit" value="<?php echo $args['primary_btn_word']; ?>" class="um-button" /></div>
			<div class="um-right um-half"><a href="<?php echo um_edit_my_profile_cancel_uri(); ?>" class="um-button um-alt"><?php echo $args['secondary_btn_word']; ?></a></div>
			
			<?php } else { ?>
			
			<div class="um-center"><input type="submit" value="<?php echo $args['primary_btn_word']; ?>" class="um-button" /></div>
			
			<?php } ?>
			
			<div class="um-clear"></div>
			
		</div>
	
		<?php
	}
	
	/***
	***	@display the available profile tabs
	***/
	add_action('um_profile_navbar', 'um_profile_navbar');
	function um_profile_navbar( $args ) {
		global $ultimatemember;
		
		$tabs = $ultimatemember->profile->tabs_active();

		$tabs = apply_filters('um_user_profile_tabs', $tabs );
		
		$ultimatemember->user->tabs = $tabs;
		
		if ( count( $tabs ) <= 1 ) return;
		
		$active_tab = $ultimatemember->profile->active_tab();

		if ( !isset( $tabs[$active_tab] ) )
			$active_tab = 'main';
			$ultimatemember->profile->active_tab = $active_tab;
			$ultimatemember->profile->active_subnav = null;

		?>
		
		<div class="um-profile-nav">
		
			<?php foreach( $tabs as $id => $tab ) {
				
				$nav_link = $ultimatemember->permalinks->get_current_url( get_option('permalink_structure') );
				$nav_link = remove_query_arg( 'um_action', $nav_link );
				$nav_link = remove_query_arg( 'subnav', $nav_link );
				$nav_link = add_query_arg('profiletab', $id, $nav_link )
				?>
			
			<div class="um-profile-nav-item <?php if ( !um_get_option('profile_menu_icons') ) { echo 'without-icon'; } ?> <?php if ( $id == $active_tab ) { echo 'active'; } ?>">
				<a href="<?php echo $nav_link; ?>" title="<?php echo $tab['name']; ?>">

					<i class="<?php echo $tab['icon']; ?>"></i>
					
					<span class="uimob500-hide uimob340-hide uimob800-hide title"><?php echo $tab['name']; ?></span>
					
					<?php if ( um_get_option('profile_menu_counts') && isset( $tab['count'] ) ) { ?>
					<span class="uimob500-hide uimob340-hide uimob800-hide count"><?php echo $tab['count']; ?></span>
					<?php } ?>
					
				</a>
			</div>
			
			<?php } ?>
			
			<div class="um-clear"></div>
		</div>
	
	<?php foreach( $tabs as $id => $tab ) {
			
			if ( isset( $tab['subnav'] ) && $active_tab == $id ) {
				
				$active_subnav = ( $ultimatemember->profile->active_subnav() ) ? $ultimatemember->profile->active_subnav() : $tab['subnav_default'];

				echo '<div class="um-profile-subnav">';
				foreach( $tab['subnav'] as $id => $subtab ) {
				
				?>
					
					<a href="<?php echo add_query_arg('subnav', $id ); ?>" class="<?php if ( $active_subnav == $id ) echo 'active'; ?>"><?php echo $subtab; ?></a>
					
					<?php
					
				}
				echo '</div>';
			}
			
		}
	
	}