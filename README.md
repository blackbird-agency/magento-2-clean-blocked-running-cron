# Clean blocked running cron

[![License: MIT](https://img.shields.io/github/license/blackbird-agency/magento-2-category-empty-button.svg?style=flat-square)](./LICENSE)

This module add a CLI command which allows you to end a CRON job that is running for a defined time.
The free source is available at the [GitHub repository](https://github.com/blackbird-agency/magento-2-clean-blocked-running-cron).

## Requirements

- PHP >= 7.1
- Magento >= 2.2

## Setup

### Get the package

**Zip Package:**

Unzip the package in app/code/Blackbird/CleanBlockedRunningCron, from the root of your Magento instance.

**Composer Package:**

```
composer require blackbird/clean-blocked-running-cron
```

### Install the module

Go to your Magento root, then run the following Magento command:

```
php bin/magento setup:upgrade
```

**If you are in production mode, do not forget to recompile and redeploy the static resources, or to use the `--keep-generated` option.**

### Command

This extension gives you a new CLI command for your Magento :
```
php <magento-root-dir>/bin/magento cron:blocked:clean -H <hours> -M <minutes> -c <job code(s)>
```

#### Parameters

- ```-H``` or ```--hours``` and ```-M``` or ```--minutes``` allows you to define how long after execution the jobs are considered blocked.
- ```-c``` or ```--cron``` allows you to define which cron jobs need to be killed after H hours and M minutes execution. It's possible to define multiple jobs separated by a comma.

The idea is to use this command in your crontab for each job which sometime blocked.  
**Be carefully to define the appropriate hours and minutes for the defined cron job codes to not kill real running jobs.**

## Support

- If you have any issue with this code, feel free to [open an issue](https://github.com/blackbird-agency/magento-2-clean-blocked-running-cron/issues/new).
- If you want to contribute to this project, feel free to [create a pull request](https://github.com/blackbird-agency/magento-2-clean-blocked-running-cron/compare).

## Contact

For further information, contact us:

- by email: hello@bird.eu
- or by form: [https://black.bird.eu/en/contacts/](https://black.bird.eu/contacts/)

## Authors

- [**Thibaud Ritzenthaler**](https://github.com/thibaud-bird) - *Maintainer* - 
- [**Blackbird Team**](https://github.com/blackbird-agency) - *Contributor* - 

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

***That's all folks!***

