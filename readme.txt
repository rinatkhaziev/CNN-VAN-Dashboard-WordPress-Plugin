=== CNN VAN Affliate Dashboard ===
Developed By: thePlatform for Media, Inc.
Tags: embedding, video, embed, portal, theplatform, shortcode, cnn, van
Requires at least: 3.7
Tested up to: 3.9
Stable tag: 1.0.0

Embed content from the CNN Video Affiliate Network.

== Description ==
View your VAN content and easily embed the CNN Video player into your Wordpress posts

== Installation ==
Copy the folder "cnn-van-dashboard" with all included files into the "wp-content/plugins" folder of WordPress. Activate the plugin and set your feed id and affiliate name in the plugin settings.

== Screenshots ==

== Changelog ==

= 2.0.0 =
Updated for compatability

== Configuration ==

= CNN VAN Affiliate Options =
CNN VAN Feed PID - Your MPX Feed Public ID	
CNN VAN Affiliate ID- Your CNN VAN Affiliate identifier
Default Video Width	- The default Video embed width
Auto Start Videos - Whether or not to auto start videos

= Filters =
van_embed_code - The CNN Embed code
van_feed_url - The Base VAN Feed URL

= Capabilities filters = 
van_embedder_cap (default - edit_posts) - Embed Videos in posts
van_admin_cap  (default - manage_options) - Configure the plugin

= Shortcode parameters =
id - CNN VAN Video ID
width - Override the default width
autostart - true/false, override the default autostart setting