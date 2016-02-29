# wordpress-boilerplate

Quick setup opinionated WordPress boilerplate


## Features

- All dependencies (wordpress-core, mu-plugins, plugins) managed with Composer
- Multiple languages
    + Out of the box: French & English
    + Default language: **French**
- ACF Pro (see installation below)
- Gravity Forms (see installation below)

## Installation

1. Add this repo as a remote at the trunk of your project. It assumes your project's public directory is [www](www). You can also simply paste the files in. Whichever.
2. Start by replacing any mentions of `boilerplate` with whatever fits your need. Search all files! **Case sensitive**
3. You can install [`example.sql`](example.sql) for a barebones database to kick start your project. However, you'll need to change a few things.
    - URLs (replace `boilerplate.dev` with your own local dev environment schema)
    - **Licenses and keys**
        + `acf_pro_license`
        + `rg_gforms_key`
        + `rg_gforms_captcha_public_key`
        + `rg_gforms_captcha_private_key`
4. Now that your database is set, save [`config/sample.php`](config/sample.php) as `config/local.php`. This file won't be committed and will contain your database credentials. Add them here.
5. Change your _Authentication Keys & Salts_ in [`config/shared.php`](config/shared.php).
6. Run `composer install` at the trunk.
7. All set. Default admin user is `boilerplate_user`:`boilerplate_password`. You should obviously create your own user and remove this one.
