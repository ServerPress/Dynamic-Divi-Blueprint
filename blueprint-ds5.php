<?php
/**
 * Blueprint Name: WordPress Divi
 * Blueprint URI: https://github.com/ServerPress/dynamic-divi-blueprint
 * Description: Fetches and installs the latest version of WordPress, Divi, compatible with DesktopServer 5.X only.
 * Version: 1.0.1
 * Author: Gregg Franklin
 */

//  You can modify this blueprint to your liking. For example a user is created named "testadmin" with a password of "password". You can change these and the admin_email address. 
//  Also if you do not want a particular function to occur you can comment that line out by placing two fowrad slashes in front of the line. For example: // ds_cli_exec( "wp plugin update --all" ); 
//  This will no longer update all plugins.

global $ds_runtime;
/**
 * Inject a blueprint options page asking for a title, username, and password.
 */
$ds_runtime->add_action('ds_bp_options_title', function($title) {
	return 'WordPress Latest';
});

/**
 * Create the form in HTML to appear on the page.
 */
$ds_runtime->add_action('ds_card_bp_options', function($content) {
	?>
	  <p>Please fill out the credentials for your new WordPress website.</p>
	  <b-form-group
        id="bp_title_group"
        label="Title:"
        label-for="bp_title"
        description="Enter the title for your website.">
        <b-form-input
          id="bp_title"
          placeholder="Example Website"
          required></b-form-input>
      </b-form-group>
	  <b-form-group
        id="bp_username_group"
        label="Username:"
        label-for="bp_username"
        description="Enter the username for your website.">
        <b-form-input
          id="bp_username"
		  placeholder="testadmin"
          required></b-form-input>
      </b-form-group>
	  <b-form-group
        id="bp_password_group"
        label="Password:"
        label-for="bp_password"
        description="Enter the password for your website.">
        <b-form-input
          id="bp_password"
		  placeholder="password"
          required></b-form-input>
      </b-form-group>
	  <script>
		var wp_latest = null; 
		window.addEventListener("ds_ready", function() {
			wp_latest = function() {
				var obj = {
					"bp_title": document.getElementById('bp_title').value,
					"bp_username": document.getElementById('bp_username').value,
					"bp_password": document.getElementById('bp_password').value
				};
				var d = JSON.stringify(obj);
				ds.writeFile(ds.ds_folder + "/temp/wp-latest.json", d, function(){
					window.location = ds.ds_folder + "runtime/ds-web/htdocs/creating.php";
				}); 
			}
		});
	  </script>
	<?php
});

/**
 * Intercept next button in actionbar and call our own script. 
 */
$ds_runtime->add_action('ds_actionbar_bp_options', function($content) {
	return str_replace('v-on:click="next"', 'v-on:click="wp_latest()"', $content);
});

/**
 * Fetch latest WordPress, install it, update all plugins and themes,
 * change the permalink structure, and make a new homepage, etc.
 */
$ds_runtime->add_action('ds_workflow_create_done', function($results) {

	global $ds_runtime;

	// Get the values from the blueprint options form we created
	$obj = json_decode(file_get_contents(getenv('DS_FOLDER') . '/temp/wp-latest.json'));
	@unlink(getenv('DS_FOLDER') . '/temp/wp-latest.json');

	$siteName = $results['siteName'];
	$sitePath = $results['sitePath'];

	//** Fetch the latest version of WordPress
	$cmd = "wp core download";
	$ds_runtime->exec($cmd, $sitePath);

	/* Install WordPress
 	 *
     * You can change the title, admin_user, admin_password, admin_email
     */ 
	$cmd = "wp core install --url=$siteName --title='" . $obj->bp_title . "' --admin_user=" . $obj->bp_username . " --admin_password=" . $obj->bp_password . " --admin_email=" . $obj->bp_username . "@$siteName";
	$ds_runtime->exec($cmd, $sitePath);

	//** Update All Plugins
	$cmd = "wp plugin update --all";
	$ds_runtime->exec($cmd, $sitePath);

	//** Update All Themes
	$cmd = "wp theme update --all";
	$ds_runtime->exec($cmd, $sitePath);

	//** Change the tagline
	$cmd = "wp option update blogdescription 'The sites tagline'";
	$ds_runtime->exec($cmd, $sitePath);

	//** Change Permalink structure
	$cmd = "wp rewrite structure '/%postname%' --quiet";
	$ds_runtime->exec($cmd, $sitePath);

	//** Discourage search engines from indexing this site
	$cmd = "wp option update timezone_string 'America/Los_Angeles'";
	$ds_runtime->exec($cmd, $sitePath);
	
	//** Remove Default Themes (Except twentytwentytwo)
	//** twentytwenty
	$cmd = "wp theme delete twentytwenty";
	$ds_runtime->exec($cmd, $sitePath);
	//** twentytwentyone
	$cmd = "wp theme delete twentytwentyone";
	$ds_runtime->exec($cmd, $sitePath);

	//** Remove Plugins
	//** Akismet
	$cmd = "wp plugin delete akismet";
	$ds_runtime->exec($cmd, $sitePath);
	//** Hello Dolly
	$cmd = "wp plugin delete hello";
	$ds_runtime->exec($cmd, $sitePath);
	
	//** Remove example comment
	$cmd = "wp comment delete 1";
	$ds_runtime->exec($cmd, $sitePath);	

	//** Remove Default Post
	//** Hello World!
	$cmd = "wp post delete 1 --force";
	$ds_runtime->exec($cmd, $sitePath);	
	
	//** Remove Default Page
	//** Sample Page
	$cmd = "wp post delete 2 --force";
	$ds_runtime->exec($cmd, $sitePath);	
	
	//** Install & Activate Divi Builder Plugin located on the Computer - Use Path based on DS-CLI 
	/* Mac example */
	//$cmd = "cp /Volumes/Data/Premium_plugins\divi-builder.zip ./; wp plugin install divi-builder.zip --activate; rm divi-builder.zip";
	//$ds_runtime->exec($cmd, $sitePath);

	/* Windows example */
	$cmd = "cp 'C:/Premium_plugins/divi-builder.zip' ./; wp plugin install divi-builder.zip --activate; rm divi-builder.zip";
	$ds_runtime->exec($cmd, $sitePath);

	//** Add license
	$cmd = "wp option add et_automatic_updates_options '{"serverpress": "d253b42255ca506650ba126feaee9395cf25dd1f"}' --format=json";
	$ds_runtime->exec($cmd, $sitePath);

	//** Install Divi Theme and Activate - Use Path based on DS-CLI
	/* Mac example */
	//$cmd = "cp /Volumes/Data/Premium_theme\Divi.zip ./; wp theme install Divi.zip; rm Divi.zip";
	//$ds_runtime->exec($cmd, $sitePath);

	/* Windows example */
	$cmd = "cp 'C:/Premium_theme/Divi.zip' ./; wp theme install Divi.zip --activate; rm Divi.zip";
	$ds_runtime->exec($cmd, $sitePath);

	/* License Divi by replacing the {username} and {api key} elements to match the ones in your account(https://www.elegantthemes.com/members-area/api/) */
	$cmd = "wp option update et_automatic_updates_options '{\"username\": \"{username}\",\"api_key\": \"{api key}\" }' --format=json";
	$ds_runtime->exec($cmd, $sitePath);

	//** Make a new page for the homepage
	$cmd = "wp post create --post_type=page --post_title='Home' --post_status='publish' --post_author=1 --post_content='<!-- wp:columns {\"backgroundColor\":\"white\"} --><div class=\"wp-block-columns has-white-background-color has-background\"><!-- wp:column --><div class=\"wp-block-column\"><!-- wp:paragraph --><p><strong>CONGRATULATIONS:</strong> Your Dynamic blueprint has fetched the latest version of WordPress and created a user.<br><br>Username: <strong>" . $obj->bp_username . "</strong><br>Password: <strong>" . $obj->bp_password . "</strong></p><!-- /wp:paragraph --><!-- wp:paragraph --><p><a href=\"/wp-admin\" data-type=\"URL\" data-id=\"/wp-admin\">Log into the Dashboard</a></p><!-- /wp:paragraph --></div><!-- /wp:column --></div><!-- /wp:columns -->'"; // Home page
	$ds_runtime->exec($cmd, $sitePath);

	$cmd = "wp option update show_on_front 'page'";
	$ds_runtime->exec($cmd, $sitePath);

	$cmd = "wp option update page_on_front '4'";
	$ds_runtime->exec($cmd, $sitePath);

	//** Check if index.php unpacked okay
	if ( is_file( $sitePath . "/index.php" ) ) {

		//** Cleanup
		@unlink( $sitePath . "/blueprint.php");
		@unlink( $sitePath . "/blueprint-ds3.php");
		@unlink( $sitePath . "/blueprint-ds5.php");
		@unlink( $sitePath . "/blueprint.png");
		@unlink( $sitePath . "/index.htm");
	}

	return $results;
});
