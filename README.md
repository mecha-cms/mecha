Mecha CMS
=========

![logo](https://cloud.githubusercontent.com/assets/1669261/2845124/0fa5f700-d093-11e3-9cf8-8c892e536004.png "Mecha")

**Mecha** is a text file-based CMS. It does not require any database. All pages are saved in a `txt` file and named with specific pattern for file sorting purposes.

### Features

 1. Easy to write the articles with Markdown
 2. Live preview Markdown to HTML
 3. Widgets
 4. Plugins
 5. Multi-languages
 6. Built-In comment system
 7. Shortcodes
 8. Custom CSS and JavaScript (for art direction site)
 9. Custom fields
 10. Sitemap
 11. RSS
 12. Easy to use and well documented API
 13. Site backup and restore

### System Requirements

 - PHP 5.3.1 and above.
 - Enabled [mod_rewrite](http://httpd.apache.org/docs/current/mod/mod_rewrite.html "Apache Module mod_rewrite") module.

### Steps to Install

 1. Make sure that you already have the required components.
 2. Download **Mecha** by cloning this **GitHub** repo.
 3. Upload **Mecha** through your FTP/SFTP to the public folder/directory in your site.
 4. Type `http://example.org/install.php` in the browser for installing. Or, if you placed this CMS in a sub-folder, type `http://example.org/sub-folder-name/install.php` instead &mdash;Seriously, if you really typed domain **http://example.org** in the browser, then you really are in a big trouble!
 5. Add your name, your username and your password in the form.
 6. Log in!
 7. After logged in, you can start configuring your blog by visiting the configuration page.
 8. Create some blog posts!

### Documentation

http://mecha-cms.com

### How to Contribute?

 1. Use this CMS, do something strange until you find a bug/error on this CMS, then let me know through the [issue](https://github.com/mecha-cms/cms/issues "Mecha CMS Issues")
 2. Share this CMS project page to your friends
 3. Make some shields for Mecha
 4. Make some [plugins](https://github.com/mecha-cms/plugin "Mecha CMS Plugins") for Mecha
 5. Make a donation
 6. Give me a star!
 7. Say thanks, for motivation purpose :)

### Pull Request

If you want to do a _pull request_, make sure that you are using the latest version of this CMS. You can download it from the front page, not from the release version page. Do not do a _pull request_ from a file that is derrived from your installed Mecha CMS, your CMS version maybe already out of date. Always use fresh installed CMS. This is done to prevent ambiguous in those files that are combined. For example, if someone is editing a file, and then someone else also editing the same file, then most likely the file that has been edited at the end of time will erase the previous user edits.

### PS

I use [Zepto](https://github.com/madrobby/zepto "Zepto") and [Font Awesome](http://fortawesome.github.io/Font-Awesome "Font Awesome") resources through the public CDN from [here](http://cdnjs.com/libraries/zepto "CDNJS") and [here](http://www.bootstrapcdn.com/#fontawesome_tab "Bootstrap CDN"). I just don&rsquo;t like to create a commit that only contains version updates of external resources. It was a waste.