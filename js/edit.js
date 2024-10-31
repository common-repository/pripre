var pripre_refresh = 1000;
var pripre_changed = '';
var pripre_window = false;
var pripre_page = 1;
var pripre_page_count = 1;
var pripre_template = null;

function pripre_get_content() {
    if (typeof(tinyMCE) != "undefined" &&
        tinyMCE.activeEditor &&
        !tinyMCE.activeEditor.isHidden()) {
        return tinyMCE.activeEditor.getContent();
    }
    return wp.data.select("core/editor").getEditedPostContent();
}

function pripre_preview_images() {
	pripre_changed = '';
	_pripre_preview_images();
}

function _pripre_preview_images() {
    var template = jQuery("#pripre_template").val();
    var user_style = jQuery("#pripre_user_style").val();
    var hide_title = jQuery("#pripre_hide_title").is(":checked");
    hide_title = hide_title ? '1' : '';
    var hide_thumbnail = jQuery("#pripre_hide_thumbnail").is(":checked");
    hide_thumbnail = hide_thumbnail ? '1' : '';
    var page_break = jQuery("#pripre_page_break").val();
    var extra_css = jQuery("#pripre_extra_css").val();
    var title = wp.data.select("core/editor").getEditedPostAttribute("title");
    var content = pripre_get_content();
    if (pripre_changed == (title+content)) {
        pripre_schedule();
        return;
    }
    jQuery("#pripre_page").text('変換中...');
	jQuery("#pripre_go_left").css({"color":"LightGray"});
	jQuery("#pripre_go_leftend").css({"color":"LightGray"});
	jQuery("#pripre_go_right").css({"color":"LightGray"});
	jQuery("#pripre_go_rightend").css({"color":"LightGray"});
    jQuery.post(pripre_base+"/actions/preview-images.php",
    {
    	 "post_id": pripre_post_id,
    	 "template": template,
    	 "user_style": user_style,
    	 "hide_title": hide_title,
    	 "hide_thumbnail": hide_thumbnail,
    	 "page_break": page_break,
    	 "extra_css": extra_css,
    	 "title": title,
    	 "content": content
    },
    function(data, status) {
    	pripre_template = pripre_template_params[template];
        pripre_page = pripre_page_count = data;
        pripre_changed = title+content;
        pripre_reload();
        pripre_schedule();
    });
}

function pripre_reload() {
    jQuery("#pripre_image_wrapper").css("height", (250 * pripre_template['height'] / pripre_template['width'])+"px");
	pripre_reload_context(document);
	if (pripre_window) {
		pripre_reload_context(pripre_window.document);
	}
}

function pripre_reload_context(context) {
    var src = pripre_image_src();
    jQuery("#pripre_image", context).attr("src", src);
    jQuery("#pripre_image_wrapper", context).css("display", "block");
    jQuery("#pripre_page", context).text(pripre_page+" / "+pripre_page_count);
    if (pripre_template['page-progression'] == 'rtl') {
	    if(pripre_page >= pripre_page_count) {
	    	jQuery("#pripre_go_left", context).css({"color":"LightGray"});
	    	jQuery("#pripre_go_leftend", context).css({"color":"LightGray"});
	    }
	    else {
	    	jQuery("#pripre_go_left", context).css({"color":"inherit"});
	    	jQuery("#pripre_go_leftend", context).css({"color":"inherit"});
	    }
	    if(pripre_page <= 1) {
	    	jQuery("#pripre_go_right", context).css({"color":"LightGray"});
	    	jQuery("#pripre_go_rightend", context).css({"color":"LightGray"});
	    }
	    else {
	    	jQuery("#pripre_go_right", context).css({"color":"inherit"});
	    	jQuery("#pripre_go_rightend", context).css({"color":"inherit"});
	    }
    }
    else {
	    if(pripre_page >= pripre_page_count) {
	    	jQuery("#pripre_go_right", context).css({"color":"LightGray"});
	    	jQuery("#pripre_go_rightend", context).css({"color":"LightGray"});
	    }
	    else {
	    	jQuery("#pripre_go_right", context).css({"color":"inherit"});
	    	jQuery("#pripre_go_rightend", context).css({"color":"inherit"});
	    }
	    if(pripre_page <= 1) {
	    	jQuery("#pripre_go_left", context).css({"color":"LightGray"});
	    	jQuery("#pripre_go_leftend", context).css({"color":"LightGray"});
	    }
	    else {
	    	jQuery("#pripre_go_left", context).css({"color":"inherit"});
	    	jQuery("#pripre_go_leftend", context).css({"color":"inherit"});
	    }
    }
}

function pripre_schedule() {
	if (!pripre_auto_preview) {
		return;
	}
    jQuery.schedule({
        time:pripre_refresh, 
        func:function(inst){
        	if (!pripre_auto_preview) {
        		return;
        	}
        	_pripre_preview_images();
        }
    });
}

function pripre_image_src() {
    return pripre_base+"/actions/image.php?page="+pripre_page+"&"+(new Date().getTime());
}

function pripre_preview_simple(action) {
    var template = jQuery("#pripre_template").val();
    var user_style = jQuery("#pripre_user_style").val();
    var hide_title = jQuery("#pripre_hide_title").is(":checked");
    var hide_thumbnail = jQuery("#pripre_hide_thumbnail").is(":checked");
    var page_break = jQuery("#pripre_page_break").val();
    var extra_css = jQuery("#pripre_extra_css").val();
    var title = wp.data.select("core/editor").getEditedPostAttribute("title");
    var content = pripre_get_content();
  
    jQuery("#pripre_preview_pdf form:first-child").remove();
    jQuery("#pripre_preview_pdf").prepend("<form target='pdf' action='"+pripre_base+"/actions/"+action+"' method='post'></form>");
    var form = jQuery("#pripre_preview_pdf form:first-child");
    jQuery('<input />').attr('type', 'hidden')
		    .attr('name', 'post_id')
		    .attr('value', pripre_post_id)
		    .appendTo(form);
    jQuery('<input />').attr('type', 'hidden')
            .attr('name', 'template')
            .attr('value', template)
            .appendTo(form);
    jQuery('<input />').attr('type', 'hidden')
		    .attr('name', 'user_style')
		    .attr('value', user_style)
		    .appendTo(form);
    if (hide_title) {
	    jQuery('<input />').attr('type', 'hidden')
			    .attr('name', 'hide_title')
			    .attr('value', '1')
			    .appendTo(form);
	}
    if (hide_thumbnail) {
	    jQuery('<input />').attr('type', 'hidden')
			    .attr('name', 'hide_thumbnail')
			    .attr('value', '1')
			    .appendTo(form);
	}
    if (page_break) {
	    jQuery('<input />').attr('type', 'hidden')
			    .attr('name', 'page_break')
			    .attr('value', page_break)
			    .appendTo(form);
	}
    if (extra_css) {
	    jQuery('<input />').attr('type', 'hidden')
			    .attr('name', 'extra_css')
			    .attr('value', extra_css)
			    .appendTo(form);
	}
    jQuery('<input />').attr('type', 'hidden')
            .attr('name', 'title')
            .attr('value', title)
            .appendTo(form);
    jQuery('<input />').attr('type', 'hidden')
            .attr('name', 'content')
            .attr('value', content)
            .appendTo(form);
    jQuery('<input />').attr('type', 'submit')
    		.attr('class', 'post')
            .attr('style', 'display:none;')
            .appendTo(form);
   jQuery("#pripre_preview_pdf form:first-child .post").click();
}

function pripre_preview_pdf() {
	pripre_preview_simple('preview-pdf.php/entry.pdf');
}

function pripre_preview_html() {
	pripre_preview_simple('preview-html.php');
}

function pripre_preview_xml() {
	pripre_preview_simple('preview-xml.php');
}

function pripre_go_page(dir) {
	if (pripre_template['page-progression'] == 'rtl') {
		switch(dir) {
		case 'left':
	       if(pripre_page >= pripre_page_count) {
	           return;
	       }
	       pripre_page++;
			break;
		case 'right':
	       if(pripre_page <= 1) {
	           return;
	       }
	       pripre_page--;
			break;
		case 'leftend':
	       pripre_page = pripre_page_count;
			break;
		case 'rightend':
	       pripre_page = 1;
			break;
		}
	}
	else {
		switch(dir) {
		case 'right':
	       if(pripre_page >= pripre_page_count) {
	           return;
	       }
	       pripre_page++;
			break;
		case 'left':
	       if(pripre_page <= 1) {
	           return;
	       }
	       pripre_page--;
			break;
		case 'rightend':
	       pripre_page = pripre_page_count;
			break;
		case 'leftend':
	       pripre_page = 1;
			break;
		}
	}
    pripre_reload();
}

function pripre_reload_window() {
	pripre_reload();
	pripre_set_preview_functions(pripre_window.document);
}

function pripre_set_preview_functions(context) {
    // 左にページ移動
    jQuery("#pripre_go_left", context).click(function(){
    	pripre_go_page('left');
    });
    // 左最後に移動
    jQuery("#pripre_go_leftend", context).click(function(){
    	pripre_go_page('leftend');
    });
    // 右にページ移動
    jQuery("#pripre_go_right", context).click(function(){
    	pripre_go_page('right');
    });
    // 左最後に移動
    jQuery("#pripre_go_rightend", context).click(function(){
    	pripre_go_page('rightend');
    });
}

jQuery(function(){
    jQuery("#pripre_image").click(function(){
    	pripre_window = window.open(pripre_base+"/actions/preview.php",
    			"pripre_preview","width="+(pripre_template['width'] * 3.78 + 2)+",height="+(pripre_template['height'] * 3.78 + 24)+",resizable=no");
    	pripre_window.onload = pripre_reload_window;
    	setTimeout('pripre_reload_window()', 500);
    });
    pripre_set_preview_functions(document);
    jQuery("#pripre_template").change(function(){
    	if (pripre_window) {
    		pripre_window.close();
    		pripre_window = false;
    	}
    	var key = jQuery("#pripre_template").val();
    	var site = pripre_template_params[key]['site'];
    	var a = jQuery("#pripre_site");
    	a.children().remove();
    	if (site) {
    		a.attr('href', site);
    		a.append(jQuery('<span>スタイルの説明</span>'));
    	}
    	if (!pripre_auto_preview) {
    		return;
    	}
    	pripre_preview_images();
    });
    jQuery("#pripre_template").change();
	if (!pripre_auto_preview) {
		_pripre_preview_images();
	}
	jQuery(window).unload(function(){
		if (pripre_window) {
			pripre_window.close();
		}
	});
});
