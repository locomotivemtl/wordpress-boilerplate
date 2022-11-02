# 🚂 WordPress Project Boilerplate

A quick and opinionated WordPress boilerplate with Composer,
an easier configuration, and an improved folder structure.

> This boilerplate is based on [wp-jazz/wp-project-skeleton]
> which is derived from [Bedrock][roots/bedrock].
>
> If you have the capability, please consider
> [sponsoring Roots](https://github.com/sponsors/roots).

## Overview

This boilerplate assumes you are familiar with [wp-jazz/wp-project-skeleton]
and [Bedrock](https://docs.roots.io/bedrock/master/installation/).

Differences with [wp-jazz/wp-project-skeleton]:

* The _Web root directory_ is `www` instead of `public`.
* Includes a copy of [`wp-ajax.php`](www/wp-ajax.php), a near-identical copy
  of WordPress' [`admin-ajax.php`](https://github.com/WordPress/WordPress/blob/6.1.0/wp-admin/admin-ajax.php).
* Prepared for integration with:
  * [Activity Log][pojome/activity-log] — Plugin to monitor and log all changes and activities.
  * [Advanced Custom Fields Pro][acf] — Plugin to allow adding extra content fields.
  * [Ecocide][mcaskill/wp-ecocide] — Library to disable basic features of WordPress.
  * [Gravity Forms][gravityforms] — Plugin to allow building custom forms.
  * [Polylang Pro][polylang] — Plugin to support multilingual content.
* Includes copies of WordPress databases:
  * Unilingual (English)
  * Multilingual (English and French)

## Requirements

* PHP >= 7.4
* Composer ([Installation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx))
* Active licenses for Advanced Custom Fields Pro, Gravity Forms, and Polylang Pro.

## Installation

1. Create a new project:

    ```shell
    composer create-project locomotivemtl/wordpress-boilerplate
    ```

    Note that installation of Composer dependencies will fail because
    of the premium WordPress plugins that require license keys to be defined.

    Alternatively, clone the repository:

    ```shell
    git clone https://github.com/locomotivemtl/wordpress-boilerplate.git .
    rm -rf .git
    git init
    git add -A
    git commit -m "Initial commit"
    ```

    Or add the repository as a remote:

    ```shell
    git remote add boilerplate https://github.com/locomotivemtl/wordpress-boilerplate.git
    git fetch boilerplate main
    git merge boilerplate/main
    ```

2. Update environment variables in the `.env` file.

    Wrap values that may contain non-alphanumeric characters with quotes,
    or they may be incorrectly parsed.

    * Database variables:
        * `DB_NAME` — Database name
        * `DB_USER` — Database user
        * `DB_PASSWORD` — Database password
        * `DB_HOST` — Database host
        * Optionally, you can define `DATABASE_URL` for using a DSN instead of
            using the variables above (e.g. `mysql://user:password@127.0.0.1:3306/db_name`)
    * `WP_ENVIRONMENT_TYPE` — Set to environment (`development`, `staging`, `production`)
    * `WP_HOME` — Full URL to WordPress home (https://example.com)
    * `WP_SITEURL` — Avoid editing this variable. Full URL to WordPress including subdirectory (https://example.com/wordpress)
    * `ACF_PRO_KEY`, `GRAVITY_FORMS_KEY`, `POLYLANG_PRO_KEY` — Premium plugin license keys.
    * `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT`
        * Generate with [wp-cli-dotenv-command]
        * Generate with [our WordPress salts generator][roots/salts]

3. Supply Composer with credentials for authenticating the installation of Polylang Pro:

    This step is necessary because Polylang Pro uses
    [Easy Digital Downloads][easydigitaldownloads] (EDD) for distribution.

    ```sh
    composer config [--global] --editor --auth
    ```

    ```json
    {
        "http-basic": {
            "polylang.pro": {
                "username": "username",
                "password": "password"
            }
        }
    }
    ```

4. Add plugin(s) in `www/plugins` and `www/mu-plugins`, and theme(s) in `www/themes` either:
    * as you would for a normal WordPress site (add an exception to the `.gitignore` if you want to index them)
    * or as Composer dependencies.

5. Most projects use pretty permalinks. This requires a `.htaccess` file on
  Apache servers. This file is not indexed in Git since it can contain
  environment-specific requirements. To create or update the file (and update
  rewrite rules in the database):

    ```shell
    wp rewrite flush --hard
    ```

6. Set the document root on your Web server to Jazz's `www` folder: `/path/to/site/www/`.

7. Access WordPress admin at `https://example.com/wordpress/wp-admin/`.

If you choose to use one of the starting databases, you will need to change the
following:

* Replace the base URI:
  * `example.test`
* Add your license keys:
  * `acf_pro_license`
  * `rg_gforms_key`
  * `rg_gforms_captcha_public_key`
  * `rg_gforms_captcha_private_key`

<!-- ## Documentation -->

<!-- Boilerplate documentation is available at the repository's [GitHub Wiki](https://github.com/locomotivemtl/wordpress-boilerplate/wiki). -->

## Contributing

Contributions are welcome from everyone.
We have [contributing guidelines](CONTRIBUTING.md)
to help you get started.

## Acknowledgements

This boilerplate is based on the solid work of many that have come before me, including:

* [Bedrock][roots/bedrock]
* [wp-jazz/wp-project-skeleton]

[acf]:                            https://advancedcustomfields.com
[composer]:                       https://getcomposer.org
[easydigitaldownloads]:           https://easydigitaldownloads.com/
[gravityforms]:                   https://gravityforms.com
[mcaskill/wp-ajax]:               https://gist.github.com/mcaskill/95acb103a5e5a78a7184b38fbacfa66e
[mcaskill/wp-ecocide]:            https://github.com/mcaskill/wp-ecocide
[pojome/activity-log]:            https://github.com/pojome/activity-log
[polylang]:                       https://polylang.pro
[roots/bedrock]:                  https://github.com/roots/bedrock
[roots/salts]:                    https://roots.io/salts.html
[roots/wp-password-bcrypt]:       https://github.com/roots/wp-password-bcrypt
[vlucas/phpdotenv]:               https://github.com/vlucas/phpdotenv
[wp-cli-dotenv-command]:          https://github.com/aaemnnosttv/wp-cli-dotenv-command
[wp-jazz/wp-project-skeleton]:    https://github.com/wp-jazz/wp-project-skeleton
