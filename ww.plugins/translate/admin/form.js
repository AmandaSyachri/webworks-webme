$(function(){
	$("#page_vars_translate_page_id").remoteselectoptions({
		url:"/ww.admin/pages/get_parents.php"
	});
	$("#page_vars_translate_language").remoteselectoptions({
		url:"/ww.plugins/translate/admin/languages.php"
	});
});
