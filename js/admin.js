$(function() {
	$('.checkboxes-helpers').each(function() {
                dotclear.checkboxesHelpers(this);
        });

	$('#plugins-form').submit(function() {
		var delete_key = 'addon';
                var action = $(this).find('select[name="action"]').val();
                var count_checked = $('input[name="plugins[]"]:checked', $(this)).length;
                if (count_checked==0) {
                        return false;
                }
                if (action=='delete') {
                        if (count_checked>1) {
				delete_key += 's';
                        }

                        return window.confirm(dcckeditor_addons_confirm_delete[delete_key].replace('%s',count_checked));
                }

                return true;
        });

});
