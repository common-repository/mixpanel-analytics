<?php
/*
Plugin Name: mixpanel Analytics
Plugin URI: http://wordpressian.tk/plugins/mixpanel-analytics/
Description: Track your website visitors using <a href="http://mixpanel.com/" target="_blank">mixpanel</a>...
Version: 0.1
Author: Vlad Babii
Author URI: http://vladbabii.com
*/

function mixpanelanalytics_plugin_activate() {
	add_option('mixpanelanalytics_id','');
	add_option('mixpanelanalytics_enabled','0');
	add_option('mixpanelanalytics_loggedinlogging','1');
	
	add_option('mixpanelanalytics_track_pageviews','1');
	add_option('mixpanelanalytics_track_search_engines','1');
	add_option('mixpanelanalytics_track_browsers','1');
	add_option('mixpanelanalytics_track_os','1');
}

function mixpanelanalytics_plugin_deactivate() {
	remove_option('mixpanelanalytics_id');
	remove_option('mixpanelanalytics_enabled');
	remove_option('mixpanelanalytics_loggedinlogging','1');
	
	remove_option('mixpanelanalytics_track_pageviews');
	remove_option('mixpanelanalytics_track_search_engines');
	remove_option('mixpanelanalytics_track_browsers');
	remove_option('mixpanelanalytics_track_os');
}

function mixpanelanalytics_footer_tracking_code() {
	$enabled 			 = get_option('mixpanelanalytics_enabled','0');
	if($enabled!=='1') { 
		return;
	}
	
	$id		 			 = get_option('mixpanelanalytics_id','');
	if($id=='') {
		return;
	}
	
	$loggedinusers 		= get_option('mixpanelanalytics_loggedinlogging','1');
	if($loggedinusers=='0' && is_user_logged_in()==true) {
		return ;
	}
	
	$track=array();
	$track['pageviews']	 	= get_option('mixpanelanalytics_track_pageviews','1');
	$track['browsers']	 	= get_option('mixpanelanalytics_track_browsers','1');
	$track['searchengines'] = get_option('mixpanelanalytics_track_search_engines','1');
	$track['os'] 			= get_option('mixpanelanalytics_track_os','1');
	
	$embeded=false;
	foreach($track as $type=>$isenabled) {
		if($isenabled=='1' && function_exists('mixpanelanalytics_track_'.$type)) {
			if($embeded==false) {
				mixpanelanalytics_embed_start($id);
				$embeded=true;
			}
			$function='mixpanelanalytics_track_'.$type;
			$function();
		}
	}
	if($embded==true) {
		mixpanelanalytics_embed_end();
	}
}

function mixpanelanalytics_embed_start($id) {
?>
<script type="text/javascript"> 
var mp_protocol = (('https:' == document.location.protocol) ? 'https://' : 'http://'); 
document.write(unescape('%3Cscript src="' + mp_protocol + 'api.mixpanel.com/site_media/js/api/mixpanel.js" type="text/javascript"%3E%3C/script%3E')); 
</script>
<script type="text/javascript"> 
try {  
  var mpmetrics = new MixpanelLib('<?php echo $id; ?>'); 
} catch(err) { 
  null_fn = function () {}; var mpmetrics = {  track: null_fn,  track_funnel: null_fn,  register: null_fn,  register_once: null_fn, register_funnel: null_fn }; 
}
</script>
<?php
}

function mixpanelanalytics_embed_end() { }

function mixpanelanalytics_track_pageviews() {
?>
<script type="text/javascript">
	mpmetrics.track("page-view",{'domain':'<?php echo trim($_SERVER['HTTP_HOST'],'/.'); ?>', 'url':'<?php echo $_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']; ?>'});
</script>
<?php	
}

function mixpanelanalytics_track_browsers() {
	if(isset($_SERVER['HTTP_USER_AGENT'])) {
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		$known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape', 'konqueror', 'gecko');
		$agent = strtolower($useragent);
		$pattern = '#(?<browser>' . join('|', $known) .  ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';
		if (!preg_match_all($pattern, $agent, $matches)) { ; } else {
			$i = count($matches['browser'])-1;
		    $browser=$matches['browser'][$i];
		    $version=$matches['version'][$i];
?>
<script type="text/javascript">  
    mpmetrics.track("browsers",{'browser':'<?php echo $browser; ?>', 'browser-version':'<?php echo $browser.'-'.$version; ?>'} );
</script>
<?php		    
		}
	}
}

function mixpanelanalytics_track_searchengines() {
	if(isset($_SERVER['HTTP_REFERER'])) {
		$in['ref']  = $_SERVER['HTTP_REFERER'];
		$searchterms='';
		$sengine='';
		if (preg_match("/google\./i", $in['ref'])) {
			$q=parse_url($in['ref']);
			if(isset($q['query'])) {
				$q=$q['query'];
				parse_str($q);
				if(isset($q)) {
					$sengine='google';
					$searchterms = $q;
				}
			}
		}elseif (preg_match("/alltheweb\./i", $in['ref'])) {
			$q=parse_url($in['ref']);
			if(isset($q['query'])) {
				$q=$q['query'];
				parse_str($q);
				if(isset($q)) {
					$sengine='alltheweb';
					$searchterms = $q;
				}
			}
		}elseif (preg_match("/yahoo\./i", $in['ref'])) {
			$q=parse_url($in['ref']);
			if(isset($q['query'])) {
				$q=$q['query'];
				parse_str($q);
				if(isset($q)) {
					$sengine='yahoo';
					$searchterms = $q;
				}
			}
		}elseif (preg_match("/search\.aol\./i", $in['ref'])) {
			$q=parse_url($in['ref']);
			if(isset($q['query'])) {
				$q=$q['query'];
				parse_str($q);
				if(isset($q)) {
					$sengine='aol';
					$searchterms = $q;
				}
			}
		}elseif (preg_match("/search\.msn\./i", $in['ref'])) {
			$q=parse_url($in['ref']);
			if(isset($q['query'])) {
				$q=$q['query'];
				parse_str($q);
				if(isset($q)) {
					$sengine='msn';
					$searchterms = $q;
				}
			}
		}elseif (preg_match("/bing\./i", $in['ref'])) {
			$q=parse_url($in['ref']);
			if(isset($q['query'])) {
				$q=$q['query'];
				parse_str($q);
				if(isset($q)) {
					$sengine='bing';
					$searchterms = $q;
				}
			}
		}elseif (preg_match("/twitter\./i", $in['ref'])) {
			$sengine='twitter';
			$searchterms=$in['ref'];
		}
		if(strlen($searchterms)>0) {
?>
<script type="text/javascript">
    mpmetrics.track("search-engine",{'query':'<?php echo str_replace(array('"',"'"),array(' ',' '),$q); ?>', 'who':'<?php echo $sengine; ?>'});
</script>
<?php
		}
	} 
}

function mixpanelanalytics_track_os() {
	if(isset($_SERVER['HTTP_USER_AGENT'])) {
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		$plat='';
		if (eregi('Win',$useragent)) {
			$plat = "windows";
			}
		else if (eregi('Mac',$useragent)) {
			$plat = "macintosh";
			}
		else if (eregi('Linux',$useragent)) {
			$plat = "linux";
			}
		if($plat!=='') {
?>
<script type="text/javascript">
    mpmetrics.track("operating-system",{'platform':'<?php echo $plat ?>'});
</script>
<?php			
		}
	}
	
}

function mixpanelanalytics_options_page() {
?>
<div class="wrap">
<h2>mixpanel Analytics - Options</h2>
<h3>Real-time Analytics</h3>
<p>We get it: you want your data and you want it now. That's why we show all of your statistics in real-time. You'll never get the same treatment with Google Analytics.</p>
<h3>Extremely custom event tracking</h3>
<p>Mixpanel makes it dead-simple to track any user action you want. If you have a Facebook game, we can track when users do missions and tell you which ones are the most popular.</p>
<h3>A much better Funnel Analysis</h3>
<p>Conversion tracking is one of the most important things you can do to improve your business. Mixpanel's Funnel Analysis tool gives you insight into customer behavior as they go through your conversion process.</p>

<p>Visit the <a href="http://mixpanel.com/features/" target="_blank">mixpanel features page</a> to read about everything mixpanel can do for you</p>
<?php	
if(isset($_POST['mixpanelanalyticsupdateoptions'])) {
   $opt_list=array(
	   	'mixpanelanalytics_id'
	   ,'mixpanelanalytics_enabled'
	   ,'mixpanelanalytics_loggedinlogging'
	   ,'mixpanelanalytics_track_pageviews'
	   ,'mixpanelanalytics_track_search_engines'
	   ,'mixpanelanalytics_track_browsers'
   	   );	
   foreach($opt_list as $keynr=>$key) {
		if(isset($_POST[$key])) {
			update_option($key,strip_tags($_POST[$key]));
		}   	   
   }
}
?>
<form method="post" action="options-general.php?page=<?php echo $_GET['page']; ?>">
<?php wp_nonce_field('update-options'); ?>
<table class="form-table">

<tr valign="top">
<th scope="row">Status</th>
<td><select name="mixpanelanalytics_enabled"> 
  <?php
  $variants=array('1'=>'Enabled','0'=>'Disabled');
  foreach($variants as $value=>$text) {
  	 echo '<option value="',$value,'"';
  	 if(get_option('mixpanelanalytics_enabled')==$value) {
  	 	echo ' selected';
  	 }
  	 echo '>',$text,'</option>';
  }
  ?>
</select></td>
</tr>

<tr valign="top">
<th scope="row">Tracking ID</th>
<td><input style="width:280px; text-align:center;" type="text" name="mixpanelanalytics_id" value="<?php echo get_option('mixpanelanalytics_id'); ?>"/></td>
</tr>

<tr valign="top">
<th scope="row"> Include logged in users in statistics</th>
<td><select name="mixpanelanalytics_loggedinlogging"> 
  <?php
  $variants=array('1'=>'Yes','0'=>'No');
  foreach($variants as $value=>$text) {
  	 echo '<option value="',$value,'"';
  	 if(get_option('mixpanelanalytics_loggedinlogging')==$value) {
  	 	echo ' selected';
  	 }
  	 echo '>',$text,'</option>';
  }
  ?>
</select></td>
</tr>


<tr valign="top">
<th>Track...</th><td></td>
</tr>

<tr valign="top">
<th scope="row"> - hits (page views)</th>
<td><select name="mixpanelanalytics_track_pageviews"> 
  <?php
  $variants=array('1'=>'Yes','0'=>'No');
  foreach($variants as $value=>$text) {
  	 echo '<option value="',$value,'"';
  	 if(get_option('mixpanelanalytics_track_pageviews')==$value) {
  	 	echo ' selected';
  	 }
  	 echo '>',$text,'</option>';
  }
  ?>
</select></td>
</tr>

<tr valign="top">
<th scope="row"> - search engine visitors</th>
<td><select name="mixpanelanalytics_track_search_engines"> 
  <?php
  $variants=array('1'=>'Yes','0'=>'No');
  foreach($variants as $value=>$text) {
  	 echo '<option value="',$value,'"';
  	 if(get_option('mixpanelanalytics_track_search_engines')==$value) {
  	 	echo ' selected';
  	 }
  	 echo '>',$text,'</option>';
  }
  ?>
</select></td>
</tr>

<tr valign="top">
<th scope="row"> - browsers</th>
<td><select name="mixpanelanalytics_track_browsers"> 
  <?php
  $variants=array('1'=>'Yes','0'=>'No');
  foreach($variants as $value=>$text) {
  	 echo '<option value="',$value,'"';
  	 if(get_option('mixpanelanalytics_track_browsers')==$value) {
  	 	echo ' selected';
  	 }
  	 echo '>',$text,'</option>';
  }
  ?>
</select></td>
</tr>

<tr valign="top">
<th scope="row"> - operating system</th>
<td><select name="mixpanelanalytics_track_os"> 
  <?php
  $variants=array('1'=>'Yes','0'=>'No');
  foreach($variants as $value=>$text) {
  	 echo '<option value="',$value,'"';
  	 if(get_option('mixpanelanalytics_track_browsers')==$value) {
  	 	echo ' selected';
  	 }
  	 echo '>',$text,'</option>';
  }
  ?>
</select></td>
</tr>

</table>
<p class="submit">
<input type="hidden" name="mixpanelanalyticsupdateoptions" value="1"/>
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form></div><?php
}

function mixpanelanalytics_options_page_register() {
	add_options_page(
		'mixpanel',
		'mixpanel',
		'manage_options',
		__FILE__,
		'mixpanelanalytics_options_page'
		);
}

register_activation_hook( __FILE__, 'mixpanelanalytics_plugin_activate' );
register_deactivation_hook( __FILE__, 'mixpanelanalytics_plugin_deactivate' );
add_action('wp_footer','mixpanelanalytics_footer_tracking_code');
add_action('admin_menu','mixpanelanalytics_options_page_register');