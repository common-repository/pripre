jQuery(function(){
    jQuery('#pripre-book').sortable({
    	update: pripre_update
    });
});

function pripre_update(e, ui) {
    list = jQuery("#pripre-book");
    
    book_id = jQuery("#pripre_book_id").get(0).value;
    
    bind = 'left';
    if (jQuery('#pripre_bind_right').is(':checked')) {
    	bind = 'right';
    }
    size = jQuery("#pripre_size").get(0).value;
    color = jQuery("#pripre_color").get(0).checked ? 1 : 0;
    css = jQuery("#pripre_css").get(0).value;
    epubmeta = jQuery("#pripre_epubmeta").get(0).value;
    etemplate = jQuery("#pripre_etemplate").get(0).value;
    
    ids = list.find('span.pripre-id');
    idparam = '';
    for (i = 0; i < ids.length; ++i) {
        if (i > 0) {
        	idparam += ',';
        }
        idparam += jQuery(ids.get(i)).text();
    }
    
    styles = list.find('select.pripre-style');
    styleparam = '';
    for (i = 0; i < styles.length; ++i) {
        if (i > 0) {
        	styleparam += ',';
        }
        styleparam += jQuery(styles.get(i)).val();
    }
    
    user_styles = list.find('select.pripre-user-style');
    userstyleparam = '';
    for (i = 0; i < user_styles.length; ++i) {
        if (i > 0) {
        	userstyleparam += ',';
        }
        userstyleparam += jQuery(user_styles.get(i)).val();
    }
    jQuery.post(pripre_base+"/actions/update-book.php",
        {"book_id": book_id,
    	"bind": bind,
    	"size": size,
    	"color": color,
    	"css": css,
    	"epubmeta": epubmeta,
    	"etemplate": etemplate,
    	"ids": idparam,
    	"styles": styleparam,
    	"userstyles": userstyleparam});
}
