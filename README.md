Mecha CMS
=========

![logo](https://cloud.githubusercontent.com/assets/1669261/2845124/0fa5f700-d093-11e3-9cf8-8c892e536004.png "Mecha")

**Mecha** is a file-based CMS. It does not require any database. Almost all files are stored as plain text files which grouped into folders, which you can even edit it manually through a plain text editor application. **Mecha** focused on a principle that, a static website must be personal, portable, light and easy to be exported and backed up. However, this doesn&rsquo;t mean that **Mecha** is powerless. **Mecha** has fairly flexible set of API that you can use without having to make it bloated, keeping you happy focused on developing your own website, according to your personality.

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

### Steps to Install

 1. Make sure that you already have the required system.
 2. Download **Mecha** by cloning [this](https://github.com/mecha-cms/mecha-cms) **GitHub** repo.
 3. Upload **Mecha** through your FTP/SFTP to the public folder/directory in your site — Usually named as `public_html`, `www` or `htdocs`
 4. Type `http://example.org/install.php` in the address bar of your web browser. Or, if for some reason you have to upload this CMS in a sub-folder, then just type `http://example.org/your-sub-folder-name/install.php` &mdash; Seriously, if you really typed domain **http://example.org**, then you really are in a big trouble!
 5. Put your name, your email, your username and your password in the form provided then click **Install**.
 6. There will appear a message that you are able to log in now. Log in!
 7. Once logged in, you can start configuring the blog by visiting the configuration page.
 8. Post something!

### Demo and Documentation

http://mecha-cms.com

### How to Contribute?

 1. Use this CMS, do something strange until you find a bug/error on this CMS, then let me know through the [issue](https://github.com/mecha-cms/mecha-cms/issues "Mecha CMS Issues").
 2. Share this CMS project page to your friends.
 3. Make some shields for **Mecha**.
 4. Make some [plugins](https://github.com/mecha-cms/mecha-plugin "Mecha CMS Plugins") for **Mecha**.
 5. Make a donation.
 6. Give me a star!
 7. Say thanks, for motivation purpose :)

### Pull Request

If you want to do a _pull request_, make sure that you are using the **latest development version** of this CMS. You can download it from the front page, not from the release version page. Do not do a _pull request_ from a file that is derrived from your installed Mecha CMS, your CMS version maybe already out of date. Always use fresh installed CMS. This is done to prevent ambiguous in those files that are combined. For example, if someone is editing a file, and then someone else also editing the same file, then most likely the file that has been edited at the end of time will erase the previous user edits.

### PS

I use [Zepto](https://github.com/madrobby/zepto "Zepto") and [Font Awesome](http://fortawesome.github.io/Font-Awesome "Font Awesome") resources through the public CDN from [here](http://cdnjs.com/libraries/zepto "CDNJS") and [here](http://www.bootstrapcdn.com/#fontawesome_tab "Bootstrap CDN"). I just don’t like to create a commit that only contains version updates of external resources. That’s a waste.

### What?

 - [Interested with the text editor used by **Mecha**?](https://github.com/tovic/markdown-text-editor)