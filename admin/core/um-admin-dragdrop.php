<?php

class UM_Admin_DragDrop {

	function __construct() {
		
		add_action('admin_footer', array(&$this, 'load_field_order'), 9);
		
		add_action('wp_ajax_nopriv_update_order', array(&$this, 'update_order') );
		add_action('wp_ajax_update_order', array(&$this, 'update_order') );
		
	}
	
	/***
	***	@update order of fields
	***/
	function update_order(){
	
		global $ultimatemember;
		
		if ( !is_user_logged_in() || !current_user_can('manage_options') ) die('Please login as administrator');
		
		extract($_POST);
		
		$fields = $ultimatemember->query->get_attr('custom_fields', $form_id );
		
		$this->row_data = get_option('um_form_rowdata_'. $form_id );
		
		foreach( $fields as $key => $array ) {
			if ( $array['type'] == 'row' ) {
				$this->row_data[$key] = $array;
				unset( $fields[$key] );
			}
		}
		
		update_option('um_form_rowdata_' . $form_id , $this->row_data );
		
		foreach( $_POST as $key => $value ) {
		
			// adding rows
			if (0 === strpos($key, '_um_row_')) {
				
				$row_id = str_replace( '_um_row_', '', $key );
				
				$row_array = array(
					'type' => 'row',
					'id' => $value,
					'sub_rows' => $_POST[ '_um_rowsub_'.$row_id .'_rows' ],
					'cols' => $_POST[ '_um_rowcols_'.$row_id .'_cols' ],
				);
				
				if ( isset( $this->row_data[$key] ) ) {
					$row_args = array_merge( $this->row_data[$key], $row_array );
				} else {
					$row_args = $row_array;
				}
				
				$fields[$key] = $row_args;
				
			}
			
			// change field position
			if (0 === strpos($key, 'um_position_') ) {
				$field_key = str_replace('um_position_','',$key);
				if ( isset( $fields[$field_key] ) ) {
					$fields[$field_key]['position'] = $value;
				}
			}
			
			// change field master row
			if (0 === strpos($key, 'um_row_') ) {
				$field_key = str_replace('um_row_','',$key);
				if ( isset( $fields[$field_key] ) ) {
					$fields[$field_key]['in_row'] = $value;
				}
			}
			
			// change field sub row
			if (0 === strpos($key, 'um_subrow_') ) {
				$field_key = str_replace('um_subrow_','',$key);
				if ( isset( $fields[$field_key] ) ) {
					$fields[$field_key]['in_sub_row'] = $value;
				}
			}
			
			// change field column
			if (0 === strpos($key, 'um_col_') ) {
				$field_key = str_replace('um_col_','',$key);
				if ( isset( $fields[$field_key] ) ) {
					$fields[$field_key]['in_column'] = $value;
				}
			}
			
			// add field to group
			if (0 === strpos($key, 'um_group_') ) {
				$field_key = str_replace('um_group_','',$key);
				if ( isset( $fields[$field_key] ) ) {
					$fields[$field_key]['in_group'] = $value;
				}
			}
			
		}
		
		$ultimatemember->query->update_attr( 'custom_fields', $form_id, $fields );
				
	}
	
	/***
	***	@load form to maintain form order
	***/
	function load_field_order(){
	
		global $ultimatemember;

		?>
		
		<div class="um-col-demon-settings" data-in_row="" data-in_sub_row="" data-in_column="" data-in_group="" />
		
		<div class="um-col-demon-row" style="display:none;">
		
				<div class="um-admin-drag-row-icons">
						<a href="#" class="um-admin-drag-rowsub-add um-admin-tipsy-n" title="<?php _e('Add Row','ultimatemember'); ?>" data-row_action="add_subrow"><i class="um-icon-plus-add"></i></a>
						<a href="#" class="um-admin-drag-row-edit um-admin-tipsy-n" title="<?php _e('Edit Row','ultimatemember'); ?>" data-modal="UM_edit_row" data-modal-size="normal" data-dynamic-content="um_admin_edit_field_popup" data-arg1="row" data-arg2="<?php echo get_the_ID(); ?>"><i class="um-icon-pencil-3"></i></a>
						<span class="um-admin-drag-row-start"><i class="um-icon-cursor-move"></i></span>
						<a href="#" class="um-admin-tipsy-n" title="<?php _e('Delete Row','ultimatemember'); ?>" data-remove_element="um-admin-drag-row"><i class="um-icon-trash-bin-3"></i></a>
				</div><div class="um-admin-clear"></div>
				
				<div class="um-admin-drag-rowsubs">
				<div class="um-admin-drag-rowsub">
			
					<div class="um-admin-drag-ctrls columns">
						<a href="#" class="active" data-cols="1"></a>
						<a href="#" data-cols="2"></a>
						<a href="#" data-cols="3"></a>
					</div>

					<div class="um-admin-drag-rowsub-icons">
						<span class="um-admin-drag-rowsub-start"><i class="um-icon-cursor-move"></i></span>
						<a href="#" class="um-admin-tipsy-n" title="<?php _e('Delete Row','ultimatemember'); ?>" data-remove_element="um-admin-drag-rowsub"><i class="um-icon-trash-bin-3"></i></a>
					</div><div class="um-admin-clear"></div>

					<div class="um-admin-drag-col">
					</div>
							
					<div class="um-admin-drag-col-dynamic"></div>
							
					<div class="um-admin-clear"></div>
				
				</div>
				</div>

		</div>
		
		<div class="um-col-demon-subrow" style="display:none;">
			
			<div class="um-admin-drag-ctrls columns">
				<a href="#" class="active" data-cols="1"></a>
				<a href="#" data-cols="2"></a>
				<a href="#" data-cols="3"></a>
			</div>

			<div class="um-admin-drag-rowsub-icons">
				<span class="um-admin-drag-rowsub-start"><i class="um-icon-cursor-move"></i></span>
				<a href="#" class="um-admin-tipsy-n" title="<?php _e('Delete Row','ultimatemember'); ?>" data-remove_element="um-admin-drag-rowsub"><i class="um-icon-trash-bin-3"></i></a>
			</div><div class="um-admin-clear"></div>

			<div class="um-admin-drag-col">
			</div>
					
			<div class="um-admin-drag-col-dynamic"></div>
					
			<div class="um-admin-clear"></div>

		</div>
	
		
		<form action="" method="post" class="um_update_order">
		
			<input type="hidden" name="action" id="action" value="update_order" />

			<input type="hidden" name="form_id" id="form_id" value="<?php echo get_the_ID(); ?>" />
			
			<div class="um_update_order_fields">
			
			</div>
			
		</form>
		
		<?php
		
	}
	
}