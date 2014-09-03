# ncbi-eutils-client-php

A PHP client package for the National Center for Biotechnology Information (NCBI) Entrez Programming Utilities (E-utilities).

## Installing

Install via [Composer](http://getcomposer.org). In your project's `composer.json`:

```json
  "require": {
    "umnlib/ncbi-eutils-client": "1.0.*"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:UMNLibraries/ncbi-eutils-client-php.git"
    }
  ]
```

## Running the Tests

Running the PHPUnit tests requires configuration. Notice that `phpunit.xml.dist` contains a place to put `your-email-here` and `your-tool-name-here`. Do not modify that file! Instead, copy the file to `phpunit.xml`, which will override `phpunit.xml.dist`, and insert your organization-specific data into that file. This repository is configured to ignore `phpunit.xml`, which helps to prevent exposing sensitive information, like passwords, to public source control repositories.

## Older Versions

For older versions of this package that did not use Composer, see the `0.x.y` releases.

## Attribution

The University of Minnesota Libraries created this software for the [EthicShare](http://www.ethicshare.org/about) project.
