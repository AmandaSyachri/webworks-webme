<?php
echo admin_menu(array(
	'Dashboard'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=dashboard',
	'Send Message'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=send-message'
),$_url);