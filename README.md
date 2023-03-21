Mecha CMS
=========

Mecha is a flat-file content management system for minimalists.

[<img src="https://user-images.githubusercontent.com/1669261/119496162-69eb5180-bd8d-11eb-830c-897168f58416.png" width="127" height="46">](https://mecha-cms.com) [<img src="https://user-images.githubusercontent.com/1669261/119496168-6b1c7e80-bd8d-11eb-8ee1-33e8eb5b90ed.png" width="87" height="46">](https://mecha-cms.com/reference) [<img src="https://user-images.githubusercontent.com/1669261/119496170-6bb51500-bd8d-11eb-9d6d-9d95c0510b67.png" width="102" height="46">](https://github.com/mecha-cms/mecha/discussions)

![Code Size](https://img.shields.io/github/languages/code-size/mecha-cms/mecha?color=%23444&style=for-the-badge) ![License](https://img.shields.io/github/license/mecha-cms/mecha?color=%23444&style=for-the-badge)

[![Meme](https://user-images.githubusercontent.com/1669261/187597836-936abaa2-6898-4817-a968-346c4a678d93.png)](https://vincentdnl.com/drawings/developers-side-projects)

Front-End
---------

The [default layout](https://github.com/mecha-cms/y.log "Log Layout") uses only Serif and Mono fonts. Different
operating systems might display somewhat different results. This preview was taken through a computer with Linux
operating system. Serif font that’s displayed in the preview below should be
[DejaVu Serif](https://commons.wikimedia.org/wiki/File:DejaVuSerifSpecimen.svg "DejaVu Serif · Wikimedia Commons"):

![Front-End](https://user-images.githubusercontent.com/1669261/190838629-860bfd9c-0444-4426-990f-71a604b95c32.png)

Back-End ([Optional](https://github.com/mecha-cms/x.panel "Panel Extension"))
-----------------------------------------------------------------------------

To be able to activate the back-end feature requires you to install our
[Alert](https://github.com/mecha-cms/x.alert "Alert Extension"),
[Panel](https://github.com/mecha-cms/x.panel "Panel Extension") and
[User](https://github.com/mecha-cms/x.user "User Extension") extensions. This feature is forever optional. You can use
this feature on the local version only, and remove it on the public version to secure your website (only if you don’t
trust this extension).

![Back-End](https://user-images.githubusercontent.com/1669261/193995098-3d4ff7c3-6d49-4d77-86e6-ca3ca0039d3f.png)

Colors and font types in the control panel preview above are generated from the
[default skin](https://github.com/mecha-cms/x.panel.skin.default "Panel Skin: Default"). Without them, the display will
look like the preview below:

![Back-End](https://user-images.githubusercontent.com/1669261/193995030-9538357e-a5c4-4292-8ad2-a1e657f40acc.png)

Features
--------

 - Writing pages with ease using [Markdown](https://github.com/mecha-cms/x.markdown "Markdown Extension") extension.
 - Unlimited page children.
 - Unlimited page fields.
 - Extensible as hell.
 - Create unique design for each blog post by adding special CSS and JavaScript files using
   [Art](https://github.com/mecha-cms/x.art "Art Extension") extension.
 - Built-in commenting system using [Comment](https://github.com/mecha-cms/x.comment "Comment Extension") extension.
 - RSS and Sitemap using the [Feed](https://github.com/mecha-cms/x.feed "Feed Extension") and
   [Sitemap](https://github.com/mecha-cms/x.sitemap "Sitemap Extension") extension.
 - Easy to use and well documented API.
 - Almost everything are optional.
 - Control panel using [Panel](https://github.com/mecha-cms/x.panel "Panel Extension") extension.

Requirements
------------

 - Apache 2.4 and above, with enabled
   [`mod_rewrite`](http://httpd.apache.org/docs/current/mod/mod_rewrite.html "Apache Module `mod_rewrite`") module.
 - PHP 7.3 and above, with enabled [`dom`](http://php.net/manual/en/book.dom.php "PHP Extension `dom`"),
   [`json`](http://php.net/manual/en/book.json.php "PHP Extension `json`") and
   [`mbstring`](http://php.net/manual/en/book.mbstring.php "PHP Extension `mbstring`") extension.

Preparations
------------

~~~ php
<?php

namespace Mecha\Eva;

use Lilith;
use Throwable;

use Engine\S2Engine;
use Equipment\Type;
use Event\Start;
use Event\ThirdImpact;
use Pilot\Dummy;
use Pilot\Entry as Person;
use Plug\Dummy;
use Plug\Entry;
use Project\HumanInstrumentality;

use function in_array;

class Unit01 extends Lilith {
    protected array $armors = [];
    protected Engine $engine;
    protected Plug $plug;
    public function __construct(Engine $engine, Plug $plug) {
        parent::stopCoolingLevel(3);
        parent::stopBallastWheelsRotation();
        parent::openConnectors();
        parent::stopSignal(parent::SIGNAL_TYPE_ABORT);
        parent::prepareEntryPlug($plug);
        parent::introduceTransmissionSystem(parent::TRANSMISSION_TYPE_HYDRO);
        parent::prepareConnection();
        parent::ensurePlugDepth($plug, 20);
        parent::ensureBodyToxin(-5, 2);
        parent::lockingDownInteriorArray();
        parent::verifyLinkupSystem();
        $this->engine = $engine;
        $this->plug = $plug;
        $this->contact(new Start(3));
    }
    public function __destruct() {}
    public function compat(): array|string {
        return [Type::B, Type::D];
    }
    public function equip(Equipment $armor): void {
        if (!in_array($armor->type, (array) $this->compat())) {
            return;
        }
        $this->armors[] = $armor;
    }
    public function contact(Event $event) {
        if (1 === $event->state) {}
        if (2 === $event->state) {}
        if (3 === $event->state) {}
    }
    public function intent(): array|string {
        return [ThirdImpact::class, HumanInstrumentality::class];
    }
}

try {
    $eva = new Unit01(new S2Engine(5000), new Entry(new Person('碇 シンジ')));
    $eva->launch(new Target('K-52'));
} catch (Throwable $e) {}
~~~

Just kidding. Read on!

 1. Make sure that you already have the required components.
 2. Download the available package from the [home page](https://mecha-cms.com).
 3. Upload Mecha through your FTP/SFTP to the public folder/directory on your site, then extract it!
 4. Take a look on the available extensions and layouts that you might be interested.
 5. Upload your extension files to `.\lot\x` and your layout files to `.\lot\y`. They’re auto-loaded.
 6. Read on how to add pages and tags. Learn on how to create pages from the author by looking at the
    [source code](https://github.com/mecha-cms/site "GitHub").
 7. Install [Panel](https://github.com/mecha-cms/x.panel "Panel Extension") extension if you are stuck by doing
    everything manually. You always have the full control to remove this extension without having to worry that your
    site will stop running after doing so.

Alternatives
------------

### Command Line

This assumes that your site’s public directory is in `/srv/http`. Make sure the folder is empty, or move the existing
files to another place first. Don’t forget with that `.` at the end of the command as written in the example below, to
clone the repository into the current root folder.

#### Using Composer

~~~ .sh
cd /srv/http
composer create-project mecha-cms/mecha .
~~~

You may want to install our [Panel](https://github.com/mecha-cms/x.panel) extension as well:

~~~ .sh
composer require mecha-cms/x.panel
~~~

#### Using Git

~~~ .sh
cd /srv/http
git clone https://github.com/mecha-cms/mecha.git --depth 1 .
git submodule update --init --recursive
~~~

You may want to install our [Panel](https://github.com/mecha-cms/x.panel) extension as well. Here,
[Alert](https://github.com/mecha-cms/x.alert) and [User](https://github.com/mecha-cms/x.user) extension is required to
be installed:

~~~ .sh
git submodule add https://github.com/mecha-cms/x.alert.git --depth 1 lot/x/alert
git submodule add https://github.com/mecha-cms/x.user.git --depth 1 lot/x/user
git submodule add https://github.com/mecha-cms/x.panel.git --depth 1 lot/x/panel
~~~

### Web Browser

Download the installer file from <https://mecha-cms.com/start> and then follow the instructions.

---

Contributors
------------

This project exists and survives because of you. I would like to thank all those who have taken the time to contribute
to this project.

[![Contributors](https://opencollective.com/mecha-cms/contributors.svg?avatarHeight=24&button=false&width=890)](https://github.com/mecha-cms/mecha/graphs/contributors)

Contribute financially to keep the project domain and website accessible to everyone. The website provides complete
documentation and latest information regarding the software and future development plans. Some parts of the website also
serve to provide a clean and efficient project file download feature which is obtained by managing responses from the
[GitHub API](https://docs.github.com/en/rest/reference/repos).

### Backers

[![Contribute](https://opencollective.com/mecha-cms/individuals.svg?width=890)](https://opencollective.com/mecha-cms)

### Sponsors

[![0](https://opencollective.com/mecha-cms/organization/0/avatar.svg)](https://opencollective.com/mecha-cms/organization/0/website)
[![1](https://opencollective.com/mecha-cms/organization/1/avatar.svg)](https://opencollective.com/mecha-cms/organization/1/website)
[![2](https://opencollective.com/mecha-cms/organization/2/avatar.svg)](https://opencollective.com/mecha-cms/organization/2/website)
[![3](https://opencollective.com/mecha-cms/organization/3/avatar.svg)](https://opencollective.com/mecha-cms/organization/3/website)
[![4](https://opencollective.com/mecha-cms/organization/4/avatar.svg)](https://opencollective.com/mecha-cms/organization/4/website)
[![5](https://opencollective.com/mecha-cms/organization/5/avatar.svg)](https://opencollective.com/mecha-cms/organization/5/website)
[![6](https://opencollective.com/mecha-cms/organization/6/avatar.svg)](https://opencollective.com/mecha-cms/organization/6/website)
[![7](https://opencollective.com/mecha-cms/organization/7/avatar.svg)](https://opencollective.com/mecha-cms/organization/7/website)
[![8](https://opencollective.com/mecha-cms/organization/8/avatar.svg)](https://opencollective.com/mecha-cms/organization/8/website)
[![9](https://opencollective.com/mecha-cms/organization/9/avatar.svg)](https://opencollective.com/mecha-cms/organization/9/website)