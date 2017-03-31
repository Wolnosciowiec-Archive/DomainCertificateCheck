DomainCertificateCheck
======================

A [Zend Diagnostics](https://github.com/zendframework/ZendDiagnostics) check that notifies about the upcoming expiration of your domains.

### Configuration

- domains: List of domains eg. ['wolnosciowiec.net', 'cdn1.wolnosciowiec.net']
- daysRemainingToWarn: Days remaining to raise a warning for a domain
- daysRemainingToFail: Days remaining to raise a failure for domain, if you have Zend Diagnostics in composer then eg. the deployment could be cancelled
  and marked as failed
  
## Setup in plain PHP

```php
<?php

$runner = new Runner();

$check = new Wolnosciowiec\DomainCertificateCheck\Check\DomainCertificateCheck(
    ['wolnosciowiec.net', 'cdn1.wolnosciowiec.net'],
    14,
    3
);

$results = $runner->run();
```
  
## Integration with Symfony and [LiipMonitorBundle](https://github.com/liip/LiipMonitorBundle)

```
services:
    monitor.check.domain_certificate:
        class: Wolnosciowiec\DomainCertificateCheck\Check\DomainCertificateCheck
        arguments:
            domains: ['wolnosciowiec.net', 'cdn1.wolnosciowiec.net']
            daysRemainingToAlert: 14
            daysRemainingToWarn: 3
        tags:
            - { name: liip_monitor.check, alias: domain_certificate }
```
