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

1. Add this repo as a remote at the trunk of your project. It assumes your project's public directory is [www]. You can also simply paste the files in. Whichever.
1. Start by replacing any mentions of `boilerplate` with whatever fits your need. Search all files!
2. You can install `[example.sql]` for a barebones database to kick start your project. However, you'll need to change a few things.
    - **Licenses and keys**
        + `acf_pro_license`
        + `rg_gforms_key`
        + `rg_gforms_captcha_public_key`
        + `rg_gforms_captcha_private_key`
3. Run `composer install` at the trunk.
4. All set. Default admin user is `boilerplate_user`:`boilerplate_password`