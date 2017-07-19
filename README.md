[![Build Status](https://scrutinizer-ci.com/g/gplcart/currency/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/currency/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/currency/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/currency/?branch=master)

Currency is a [GPL Cart](https://github.com/gplcart/gplcart) module that allows you to update currency exchange rates using [Yahoo Finance feed](https://developer.yahoo.com/finance)

Features:

- Automatically update currency rates with configurable interval
- Configurable percent correction
- Configurable allowed rate derivation

Requirements:

- CURL

**Installation**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/currency`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Go to `admin/module/settings/currency` and adjust settings