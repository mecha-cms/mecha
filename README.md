Mecha CMS
=========

![logo](https://cloud.githubusercontent.com/assets/1669261/2845124/0fa5f700-d093-11e3-9cf8-8c892e536004.png "Mecha")

Mecha is a file–based CMS that survives on a principle that a database–less site should be personal, portable, light and easy to be exported and backed up. That’s why most of the projects associated with Mecha are created with personal natures and are dedicated to be used for personal purposes such as blog, journal and diary. Mecha’s market share are people with high creativity and individuals who want to dedicate themselves to the freedom of speech; people who want to share their ideas to the world, that probably don’t have much time to learn web programming languages. By introducing Mecha as files and folders that used to be seen by people everyday in their working desktop, we hope you will soon be familiar with how Mecha CMS works. Mecha is as simple as files and folders.

Yet, this doesn’t mean that Mecha is weak. Mecha has fairly flexible set of API that you can use without having to make it bloated, keeping you happy focused on developing your own site, according to your personality.

If you want to make something that is super huge with Mecha, it would be possible, but remember that Mecha wasn’t created to replace databases. Mecha is created to help people getting rid of various resources that are not needed from the start (such as databases). There will be times when you need a database, and when that time comes, just use a database. Mecha is fairly open to be extended with other database–based applications.

### Features

 1. It is easy to write an article using [Markdown](http://mecha-cms.com/article/markdown-syntax "Markdown Formatting Guide").
 2. Don’t forget the ability to turn the Markdown ON and OFF.
 3. Live preview Markdown to HTML.
 4. Lots of Widgets.
 5. Lots of Plugins.
 6. The potential to make it multi-language —depends on the existence of the translation contributors.
 7. Page speed optimization. Automatically combine and compress CSS and JavaScript assets.
 8. Built-in commenting system.
 9. Shortcodes.
 10. Custom CSS and JavaScript (for art direction site).
 11. Custom Fields.
 12. RSS and Sitemap.
 13. Easy to use and well documented API.
 14. Site backup and restore.

### System Requirements

 - PHP 5.3.1 and above.
 - Enabled [mod_rewrite](http://httpd.apache.org/docs/current/mod/mod_rewrite.html "Apache Module mod_rewrite") module.

### Getting Started

 1. Make sure that you already have the required component.
 2. Download **Mecha** by cloning [this](https://github.com/mecha-cms/mecha) **GitHub** repo.
 3. Upload **Mecha** through your FTP/SFTP to the public folder/directory in your site — Usually named as `public_html`, `www` or `htdocs`
 4. Type `http://example.org/knock.php` in the address bar of your web browser. Or, if for some reason you have to upload this CMS in a sub-folder, then just type `http://example.org/your-sub-folder-name/knock.php` &mdash; Seriously, if you really typed domain **http://example.org**, then you really are in a big trouble!
 5. Put your name, your email, your _username_ and your _password_ in the form provided then click **Install**.
 6. There will appear a message describes that you are able to log in now. Log in!
 7. Once logged in, you can start configuring the blog by visiting the configuration page.
 8. Post something!

### Demo and Documentation

http://mecha-cms.com

### How to Contribute?

 1. Use this CMS, do something strange until you find a bug/error on this CMS, then let me know through the [issue](https://github.com/mecha-cms/mecha/issues "Mecha CMS Issues").
 2. Share this CMS project page to your friends.
 3. Make some shields for **Mecha**.
 4. Make some [plugins](https://github.com/mecha-cms/mecha-plugin "Mecha CMS Plugins") for **Mecha**.
 5. Make a donation.
 6. Give me a star!
 7. Say thanks, for motivation purpose :)

### Pull Request

If you want to do a _pull request_, make sure that you are using the **latest development version** of this CMS. You can download it from the front page, not from the release version page. Do not do a _pull request_ from a file that is derived from your installed Mecha CMS, your CMS version maybe already out of date. Always use fresh installed CMS. This is done to prevent ambiguous in those files that are combined. For example, if someone is editing a file, and then someone else also editing the same file, then most likely the file that has been edited at the end of time will erase the previous user edits.

### PS

I use [Zepto](https://github.com/madrobby/zepto "Zepto") and [Font Awesome](http://fortawesome.github.io/Font-Awesome "Font Awesome") resources through the public CDN from [here](http://cdnjs.com/libraries/zepto "CDNJS") and [here](http://www.bootstrapcdn.com/#fontawesome_tab "Bootstrap CDN"). I just don’t like to create a commit that only contains version updates of external resources. That’s a waste.

### Others

 - [Interested with the text editor used by **Mecha**?](https://github.com/tovic/markdown-text-editor)
