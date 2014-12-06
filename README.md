Htime LightCMSBundle
=====================

WARNING: This bundle is still under developpement.
While it is functional, the service names, configuration options etc. may change
without worrying about BC breaks.

## Installation

### Step 1: Adding the bundle to your project

Add the repository to your composer.json file:

```json
"require": {
    "htime/light-cms-bundle": "dev-master"
}
```

Then run

```
$ php composer.phar update htime/light-cms-bundle
```

### Step 2: AppKernel.php

Enable HtimeLightCmsBundle in `app/AppKernel.php`:

```php
public function registerBundles()
{
    $bundles = array(
        ...
        new Htime\LightCmsBundle\HtimeLightCmsBundle(),
    );
}
```