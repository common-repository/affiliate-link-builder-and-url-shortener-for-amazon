<?php

/*
Plugin Name: Affiliate Link Builder and URL Shortener for Amazon
Description: When posting, trims Amazon links from queries and appends your Amazon affiliate ID (tag) at the end of each one. Then it uses the URL shortening service bit.ly to generate pretty amzn.to links.
Version: 1.0
Author: purrdev
Author URI: https://github.com/purrdev
License: GPLv2 or later
*/

defined('ABSPATH') or die();


add_action( 'admin_menu', 'affiliatelinkbuilder_add_admin_menu' );
add_action( 'admin_init', 'affiliatelinkbuilder_settings_init' );


function affiliatelinkbuilder_add_admin_menu(){
	add_options_page( 'Affiliate Link Builder', 'Affiliate Link Builder', 'manage_options', 'affiliate-link-builder', 'affiliatelinkbuilder_options_page' );
}

function affiliatelinkbuilder_settings_init(){
	register_setting( 'affiliatelinkbuilder', 'affiliatelinkbuilder_settings' );

	add_settings_section(
		'affiliatelinkbuilder_affiliatelinkbuilder_section',
		__( 'Affiliate Link Builder Settings', 'affiliatelinkbuilder' ),
		'affiliatelinkbuilder_settings_section_callback',
		'affiliatelinkbuilder'
	);

	add_settings_field(
		'affiliatelinkbuilder_bitly',
		__( 'bit.ly Access Token', 'affiliatelinkbuilder' ),
		'affiliatelinkbuilder_bitly_render',
		'affiliatelinkbuilder',
		'affiliatelinkbuilder_affiliatelinkbuilder_section'
	);

	add_settings_field( 
		'affiliatelinkbuilder_tag',
		__( 'Amazon Affiliate ID (Tag)', 'affiliatelinkbuilder' ),
		'affiliatelinkbuilder_tag_render',
		'affiliatelinkbuilder',
		'affiliatelinkbuilder_affiliatelinkbuilder_section'
	);

	/*add_settings_field(
		'affiliatelinkbuilder_catchall',
		__( 'Catch all URL', 'affiliatelinkbuilder' ),
		'affiliatelinkbuilder_catchall_render',
		'affiliatelinkbuilder',
		'affiliatelinkbuilder_affiliatelinkbuilder_section'
	);*/

	add_settings_field( 
		'affiliatelinkbuilder_nofollow',
		__( 'rel="nofollow"', 'affiliatelinkbuilder' ),
		'affiliatelinkbuilder_nofollow_render',
		'affiliatelinkbuilder',
		'affiliatelinkbuilder_affiliatelinkbuilder_section'
	);

	add_settings_field( 
		'affiliatelinkbuilder_target',
		__( 'Open links in a new tab', 'affiliatelinkbuilder' ),
		'affiliatelinkbuilder_target_render',
		'affiliatelinkbuilder',
		'affiliatelinkbuilder_affiliatelinkbuilder_section'
	);
}

function affiliatelinkbuilder_tag_render(){
	$options = get_option( 'affiliatelinkbuilder_settings' );
	?>
	<input type='text' name='affiliatelinkbuilder_settings[affiliatelinkbuilder_tag]' value='<?php echo esc_attr($options['affiliatelinkbuilder_tag']); ?>'>
	<?php
}

function affiliatelinkbuilder_bitly_render(){
	$options = get_option( 'affiliatelinkbuilder_settings' );
	?>
    <input type='text' name='affiliatelinkbuilder_settings[affiliatelinkbuilder_bitly]' value='<?php echo esc_attr($options['affiliatelinkbuilder_bitly']); ?>'>
	<?php
}

function affiliatelinkbuilder_catchall_render(){
	$options = get_option( 'affiliatelinkbuilder_settings' );
	?>
	<input type='text' name='affiliatelinkbuilder_settings[affiliatelinkbuilder_catchall]' value='<?php echo esc_attr($options['affiliatelinkbuilder_catchall']); ?>'>
	<em>Warning : <u>ALL Amazon links</u> will be redirected to this single URL! Leave blank if you don't want to.</em>
	<?php
}


function affiliatelinkbuilder_nofollow_render(){
	$options = get_option( 'affiliatelinkbuilder_settings' );
	?>
	<input type='checkbox' name='affiliatelinkbuilder_settings[affiliatelinkbuilder_nofollow]' <?php checked( $options['affiliatelinkbuilder_nofollow'], 1 ); ?> value='1'>
	<?php
}


function affiliatelinkbuilder_target_render(){
	$options = get_option( 'affiliatelinkbuilder_settings' );
	?>
	<input type='checkbox' name='affiliatelinkbuilder_settings[affiliatelinkbuilder_target]' <?php checked( $options['affiliatelinkbuilder_target'], 1 ); ?> value='1'>
	<?php
}


function affiliatelinkbuilder_settings_section_callback(){

    $instructions = '<p>When posting, trims Amazon links from queries and appends your Amazon affiliate ID (tag) at the end of each one. Then it uses the URL shortening service bit.ly to generate pretty amzn.to links. The plugin works for new posts only. It does not affect existing posts.</p>

<p>bit.ly offers click analytics (total clicks per link, referrers, locations). To see this information, go to <a href="https://bitly.com/">bit.ly</a> and log in to your account.</p>

<p>First you need to sign up for <a href="https://bitly.com/">bit.ly</a> and verify your email address. Then you\'ll be able to generate a Generic Access Token in your bit.ly profile. Copy and paste it in the first input field.</p>

<p>The next input field is for your Amazon affiliate ID, which you\'ll receive after signing up for Amazon Associates in your respective country.</p>

<p>rel="nofollow" controls whether search engines should treat the link as a recommendation for SEO purposes. Check to disable (recommended).</p>

<p>"Open links in a new tab" makes each of your affiliate links open in a new browser tab by adding target="blank" to your affiliate links.</p>';

	//echo __( 'Affiliate Link Builder will automatically add or edit the "tag" variable in each Amazon links in your posts. Here you can tell the plugin which tag he will add and if the links should be in nofollow and/or opened in a new tab.<br /><br /><strong>Catch all : </strong>If you want to catch all Amazon links and redirect them to a single URL, a links proxy for example, just add this URL in the appropriate field below. Leave it blank if you don\'t want to, the plugin will still replace all tags as usual.', 'wordpress' );
    echo __( $instructions, 'wordpress' );
}

function affiliatelinkbuilder_options_page(){
	?>
	<form action='options.php' method='post'>		
		<?php
		settings_fields( 'affiliatelinkbuilder' );
		do_settings_sections( 'affiliatelinkbuilder' );
		submit_button();
		?>
	</form>
	<?php
}

function AddTag($content){
	$options_affiliatelinkbuilder = get_option('affiliatelinkbuilder_settings');
	$catchall_option = $options_affiliatelinkbuilder['affiliatelinkbuilder_catchall'];
	
	if(isset($options_affiliatelinkbuilder) && $options_affiliatelinkbuilder['affiliatelinkbuilder_tag'] != ""){
		$Tagaffiliatelinkbuilder = $options_affiliatelinkbuilder['affiliatelinkbuilder_tag'];
	}else{
		//If it can't find any tag (add it in "affiliatelinkbuilder options page") it will use this one, the author's one. Just add // before $Tagaffiliatelinkbuilder to disable this.
		//$Tagaffiliatelinkbuilder = 'purrdev';
	}
	
	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    if(preg_match_all("/$regexp/siU", $content, $link_matches, PREG_SET_ORDER)) {
		foreach($link_matches as $match) {

			if(preg_match('/(.*)amazon\.(com|co.uk|de|fr|es|it|co.jp|ca)+/i', $match[2])){
				$thelink = str_replace('&#038;',"&amp;",$match[2]);
				$parsed = parse_url($thelink);
				$options_attr = "";
				
				//Set up the right tag depending on the country
				//TODO 
				
				//Replacing tag if it already exists in URL
				/*if(preg_match('#tag#i',$parsed['query'])){
					$query_string = html_entity_decode($parsed['query']);
					parse_str($query_string, $variables);
					
					
					$variables["tag"] = $Tagaffiliatelinkbuilder;
					
					$new_query = http_build_query($variables, '', '&amp;');
					
					if($options_affiliatelinkbuilder['affiliatelinkbuilder_nofollow'] == '1'){
						$options_attr .= ' rel="nofollow"';
					}
					if($options_affiliatelinkbuilder['affiliatelinkbuilder_target'] == '1'){
						$options_attr .= ' target="_blank"';
					}
					$newlink = '<a href="'.$parsed['scheme'].'://'.$parsed['host'].$parsed['path'].'?'.$new_query.'"'.$options_attr.'>'.$match[3].'</a>';
				}else{*/
					if($options_affiliatelinkbuilder['affiliatelinkbuilder_nofollow'] == '1'){
						$options_attr .= ' rel="nofollow"';
					}
					if($options_affiliatelinkbuilder['affiliatelinkbuilder_target'] == '1'){
						$options_attr .= ' target="_blank"';
					}
					//Check if link have a query
					if (isset($parsed['query'])){
						//$newlink = '<a href="'.$thelink.'&amp;tag='.$Tagaffiliatelinkbuilder.'"'.$options_attr.'>'.$match[3].'</a>';

                        $thelink = substr($thelink, 0, strpos($thelink,"?"));
					/*} else {*/
					}

                    $thelink = $thelink.'?tag='.$Tagaffiliatelinkbuilder;


                    if(isset($options_affiliatelinkbuilder) && $options_affiliatelinkbuilder['affiliatelinkbuilder_bitly'] != "") {
                        $accessToken = $options_affiliatelinkbuilder['affiliatelinkbuilder_bitly'];
                        $thelink = file_get_contents("https://api-ssl.bitly.com/v3/shorten?access_token=$accessToken&longUrl=".urlencode($thelink)."&format=txt");
                    }

                    $newlink = '<a href="'.$thelink.'"'.$options_attr.'>'.$match[3].'</a>';
				//} else
				//If the catch all option is filled, plugin will redirect all links to the catch all URL
				if($catchall_option != ''){
					$newlink = '<a href="'.$catchall_option.'"'.$options_attr.'>'.$match[3].'</a>';
					$content = str_replace($match[0], $newlink, $content);
				}else{
					$content = str_replace($match[0], $newlink, $content);	
				}
			}
		}	
	}
	return $content;
}

add_filter( 'the_content', 'AddTag');

?>