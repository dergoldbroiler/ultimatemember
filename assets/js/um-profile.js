jQuery(document).ready(function() {

	if ( jQuery('.um-profile.um-viewing .um-profile-body').length && jQuery('.um-profile.um-viewing .um-profile-body').find('.um-field').length == 0 ) {
		jQuery('.um-profile-note').show();
	}
	
	jQuery(document).on('click', '.um-profile-save', function(e){
		e.preventDefault();
		jQuery(this).parents('.um').find('form').submit();
		return false;
	});
	
	jQuery(document).on('click', '.um-profile-edit-a', function(e){
		jQuery(this).addClass('active');
	});

	jQuery(document).on('click', '.um-cover a, .um-photo a', function(e){
		e.preventDefault();
		return false;
	});

	jQuery(document).on('click', '.um-photo-modal', function(e){
		var photo_src = jQuery(this).attr('data-src');
		um_new_modal('um_view_photo', 'fit', true, photo_src );
	});

	jQuery(document).on('click', '.um-reset-profile-photo', function(e){
		
		jQuery('.um-profile-photo-img img').attr('src', jQuery(this).attr('data-default_src') );
		
		user_id = jQuery(this).attr('data-user_id');
		metakey = 'profile_photo';
		
		jQuery.ajax({
			url: ultimatemember_ajax_url,
			type: 'post',
			data: {
				action: 'ultimatemember_delete_profile_photo',
				metakey: metakey,
				user_id: user_id
			}
		});
		
	});

	jQuery(document).on('click', '.um-reset-cover-photo', function(e){
		
		jQuery('.um-cover-overlay').hide();
		
		jQuery('.um-cover-e').html('<a href="#" class="um-cover-add um-manual-trigger" data-parent=".um-cover" data-child=".um-btn-auto-width"><span class="um-cover-add-i"><i class="um-icon-plus um-tip-n" title="Upload a cover photo"></i></span></a>');
		
		jQuery('.um-dropdown').hide();
		
		um_responsive();
		
		user_id = jQuery(this).attr('data-user_id');
		metakey = 'cover_photo';
		
		jQuery.ajax({
			url: ultimatemember_ajax_url,
			type: 'post',
			data: {
				action: 'ultimatemember_delete_cover_photo',
				metakey: metakey,
				user_id: user_id
			}
		});
		
	});

});